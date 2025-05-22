<?php

namespace App\Jobs;

use App\Http\Controllers\CyberBuddyNextGenController;
use App\Http\Procedures\TheCyberBriefProcedure;
use App\Http\Requests\ConverseRequest;
use App\Listeners\EndVulnsScanListener;
use App\Models\Collection;
use App\Models\Conversation;
use App\Models\File;
use App\Models\TimelineItem;
use App\Rules\IsValidCollectionName;
use App\User;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use League\HTMLToMarkdown\HtmlConverter;
use Webklex\IMAP\Facades\Client;
use Webklex\PHPIMAP\Attachment;

class ProcessIncomingEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const string SENDER_CYBERBUDDY = 'cyberbuddy@cywise.io';
    const string SENDER_MEMEX = 'memex@cywise.io';
    const string URL_PATTERN = "/(?:(?:https?|ftp):\/\/)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:\/[^\"\'\s]*)?/uix";

    public $tries = 1;
    public $maxExceptions = 1;
    public $timeout = 3 * 180; // 9mn

    /**
     * Based on WordPress' _extract_urls function (https://github.com/WordPress/WordPress/blob/master/wp-includes/functions.php),
     * but using the regular expression by @diegoperini (https://gist.github.com/dperini/729294) – which is close to the perfect
     * URL validation regex (https://mathiasbynens.be/demo/url-regex)
     */
    public static function extractAndSummarizeHyperlinks(string $text): array
    {
        preg_match_all(self::URL_PATTERN, $text, $matches);
        $urls = array_values(array_unique(array_map('html_entity_decode', $matches[0])));

        $prompt = "
             You are a summarizer. You write a summary of the input using following steps: 
             
             1. **Analyze the input text and generate 5 essential questions** that, when answered, comprehensively capture 
               the main points and core meaning of the text. Aim for questions that dig deeper into the content and avoid 
               redundancy.
             2. **Guidelines for Formulating Questions:**
               2.1. Address the central theme or argument.
               2.2. Identify key supporting ideas.
               2.3. Highlight important facts or evidence.
               2.4. Reveal the author’s purpose or perspective.
               2.5. Explore any significant implications or conclusions.
             3. **Answer Each Question in Detail:** Provide thorough, clear answers, maintaining a balance between depth 
                and clarity.
             4. **Final Summary:** Conclude with a short summary that encapsulates the core message of the text. Include 
                a specific example to illustrate your point.

             [TEXT]
        ";
        $tcb = new TheCyberBriefProcedure();
        $result = [];

        foreach ($urls as $url) {

            $url = trim($url);

            if (!empty($url)) {
                try {
                    $request = new Request();
                    $request->replace([
                        'url_or_text' => $url,
                        'prompt' => $prompt,
                    ]);
                    $summary = $tcb->summarize($request)['summary'] ?? '';
                    $result[] = [
                        'url' => $url,
                        'summary' => empty($summary) ? "{$url} could not be accessed or summarized." : $summary,
                    ];
                } catch (\Exception $exception) {
                    Log::error($exception->getMessage());
                    $result[] = [
                        'url' => $url,
                        'summary' => "{$url} could not be accessed or summarized.",
                    ];
                }
                if (count($result) > 0) {
                    Log::debug($result[count($result) - 1]);
                }
            }
        }
        return $result;
    }

    public function __construct()
    {
        //
    }

    public function handle()
    {
        try {

            /** @var \Webklex\PHPIMAP\Client $client */
            $client = Client::account('default');
            $client->connect();

            /** @var \Webklex\PHPIMAP\Support\FolderCollection $folders */
            $folders = $client->getFolders();

            /** @var \Webklex\PHPIMAP\Folder $folder */
            foreach ($folders as $folder) {
                if ($folder->name !== 'INBOX') {
                    continue;
                }

                /** @var \Webklex\PHPIMAP\Support\MessageCollection $messages */
                $messages = $folder->messages()->all()->get();

                /** @var \Webklex\PHPIMAP\Message $message */
                foreach ($messages as $message) {

                    $to = $message->getTo()->all();
                    $from = $message->getFrom()->all();
                    $subject = $message->getSubject()->toString();
                    $isCyberBuddy = collect($to)->map(fn(\Webklex\PHPIMAP\Address $address) => $address->mail)->contains(self::SENDER_CYBERBUDDY);
                    $isMemex = collect($to)->map(fn(\Webklex\PHPIMAP\Address $address) => $address->mail)->contains(self::SENDER_MEMEX);

                    if (!$isCyberBuddy && !$isMemex) {
                        continue;
                    }

                    Log::debug("From: {$from[0]->mail}\nTo: {$to[0]->mail}\nSubject: {$subject}");

                    if (count($from) !== 1) {
                        Log::error('Message from multiple addresses!');
                        continue;
                    }

                    // Search the user who sent the email in the database
                    /** @var \Webklex\PHPIMAP\Address $address */
                    $address = $from[0];

                    // Create shadow profile
                    $user = User::getOrCreate($address->mail);

                    Auth::login($user); // otherwise the tenant will not be properly set

                    // Ensure all prompts are properly loaded
                    /* if (Prompt::count() >= 4) {
                        Log::warning($subject);
                        Log::warning("Some prompts are not ready yet. Skipping email processing for now.");
                        continue;
                    } */

                    // Ensure all collections are properly loaded
                    if (File::where('is_deleted', false)->get()->contains(fn(File $file) => !$file->is_embedded)) {
                        Log::warning("Some collections are not ready yet. Skipping email processing for now.");
                    } else if ($isCyberBuddy) {
                        $this->cyberBuddy($user, $message);
                    } else if ($isMemex) {
                        $this->memex($user, $message);
                    }
                }
            }

            $client->disconnect();

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    private function cyberBuddy(User $user, \Webklex\PHPIMAP\Message $message): void
    {
        if ($message->hasTextBody()) {
            $body = $message->getTextBody();
        } else if ($message->hasHTMLBody()) {
            $body = $message->getHTMLBody();
        } else {
            $body = "";
        }

        // Extract the thread id in order to be able to load the existing conversation
        // If the thread id cannot be found, a new conversation is created
        $threadId = null;
        $matches = [];

        preg_match_all("/\s*thread_id=(?<threadid>[a-zA-Z0-9]{10})\s*/i", $body, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (!empty($match['threadid'])) {
                $threadId = $match['threadid'];
                break;
            }
        }
        if (empty($threadId)) {
            $threadId = Str::random(10);
        }

        /** @var Conversation $conversation */
        $conversation = Conversation::where('thread_id', $threadId)
            ->where('format', Conversation::FORMAT_V1)
            ->where('created_by', $user->id)
            ->first();

        $conversation = $conversation ?? Conversation::create([
            'thread_id' => $threadId,
            'dom' => json_encode([]),
            'autosaved' => true,
            'created_by' => $user->id,
            'format' => Conversation::FORMAT_V1,
        ]);

        if ($message->hasTextBody()) {
            // Remove previous messages i.e. rows starting with >
            $body = trim(preg_replace("/^(>.*)|(On\s+.*\s+wrote:)[\n\r]?$/im", '', $message->getTextBody()));
        } else if ($message->hasHTMLBody()) {
            $body = trim((new HtmlConverter())->convert($message->getHTMLBody()));
        } else {
            $body = "";
        }

        Log::debug("body={$body}");

        // Call CyberBuddy
        $request = new ConverseRequest();
        $request->replace([
            'thread_id' => $threadId,
            'directive' => $body,
        ]);

        $controller = new CyberBuddyNextGenController();
        $response = $controller->converse($request, true);
        $json = json_decode($response->content(), true);
        $subject = $message->getSubject()->toString();
        $body = $json['answer']['html'] ?? '';

        EndVulnsScanListener::sendEmail(
            self::SENDER_CYBERBUDDY,
            $user->email,
            "Re: {$subject}",
            "CyberBuddy vous répond !",
            "
                {$body}
                <p>Pour importer tes propres documents et profiter pleinement des capacités de CyberBuddy, ton assistant Cyber, finalise ton inscription à Cywise :</p>
            ",
            route('password.reset', [
                'token' => app(PasswordBroker::class)->createToken($user),
                'email' => $user->email,
                'reason' => 'Finalisez votre inscription en créant un mot de passe',
                'action' => 'Créer mon mot de passe',
            ]),
            "je me connecte à Cywise",
            "
              <p>Je reste à ta disposition pour toute question ou assistance supplémentaire. Merci encore pour ta confiance en Cywise !</p>
              <p>Bien à toi,</p>
              <p>CyberBuddy</p>
              <span style='color:white'>thread_id={$threadId}</span>
            ",
        );

        if (!$message->move('CyberBuddy')) {
            Log::error('Message could not be moved to the CyberBuddy folder!');
        }
    }

    private function memex(User $user, \Webklex\PHPIMAP\Message $message): void
    {
        if ($message->hasTextBody()) {
            // Remove previous messages i.e. rows starting with >
            $body = trim(preg_replace("/^(>.*)|(On\s+.*\s+wrote:)[\n\r]?$/im", '', $message->getTextBody()));
        } else if ($message->hasHTMLBody()) {
            $body = trim((new HtmlConverter())->convert($message->getHTMLBody()));
        } else {
            $body = "";
        }
        if (!empty($body)) {

            $item = TimelineItem::createNote($user, $body, $message->getSubject()->toString());
            $summaries = self::extractAndSummarizeHyperlinks($body);

            foreach ($summaries as $summary) {
                TimelineItem::createNote($user, $summary['summary'], $summary['url']);
            }
            if ($message->hasAttachments()) {

                $collection = $this->getOrCreateCollection("privcol{$user->id}", 0);

                if ($collection) {
                    $message->attachments()->each(function (Attachment $attachment) use ($collection) {
                        if (!$attachment->save("/tmp/")) {
                            Log::error("Attachment {$attachment->name} could not be saved!");
                        } else {
                            $path = "/tmp/{$attachment->filename}";
                            // TODO : deal with duplicate files using the md5/sha1 file hash
                            $url = \App\Http\Controllers\CyberBuddyController::saveLocalFile($collection, $path);
                            unlink($path);
                        }
                    });
                }
            }
        }
        if (!$message->move('Memex')) {
            Log::error('Message could not be moved to the Memex folder!');
        }
    }

    private function getOrCreateCollection(string $collectionName, int $priority): ?Collection
    {
        /** @var \App\Models\Collection $collection */
        $collection = Collection::where('name', $collectionName)
            ->where('is_deleted', false)
            ->first();
        if (!$collection) {
            if (!IsValidCollectionName::test($collectionName)) {
                Log::error("Invalid collection name : {$collectionName}");
                return null;
            }
            $collection = Collection::create([
                'name' => $collectionName,
                'priority' => max($priority, 0),
            ]);
        }
        return $collection;
    }
}
