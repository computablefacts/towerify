<?php

namespace App\Jobs;

use App\Http\Controllers\CyberBuddyNextGenController;
use App\Http\Requests\ConverseRequest;
use App\Listeners\EndVulnsScanListener;
use App\Models\Collection;
use App\Models\Conversation;
use App\Models\File;
use App\Models\Invitation;
use App\Models\Prompt;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TimelineItem;
use App\Models\YnhFramework;
use App\Rules\IsValidCollectionName;
use App\User;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Konekt\User\Models\InvitationProxy;
use Konekt\User\Models\UserType;
use Webklex\IMAP\Facades\Client;
use Webklex\PHPIMAP\Attachment;

class ProcessIncomingEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const string SENDER_CYBERBUDDY = 'cyberbuddy@cywise.io';
    const string SENDER_MEMEX = 'memex@cywise.io';

    public $tries = 1;
    public $maxExceptions = 1;
    public $timeout = 3 * 180; // 9mn

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
                    $cc = $message->getCc()->all();
                    $bcc = $message->getBcc()->all();
                    $isCyberBuddy = collect($to)->contains(self::SENDER_CYBERBUDDY);
                    $isMemex = collect($to)->contains(self::SENDER_MEMEX);

                    if (!$isCyberBuddy && !$isMemex) {
                        continue;
                    }
                    if (count($from) !== 1) {
                        Log::error($message->getSubject());
                        Log::error('Message from multiple addresses!');
                        continue;
                    }

                    // Search the user who sent the email in the database
                    /** @var \Webklex\PHPIMAP\Address $address */
                    $address = $from[0];

                    // Create shadow profile
                    $user = $this->getOrCreateUser($address->mail);

                    // Ensure all prompts are properly loaded
                    /* if (Prompt::count() >= 4) {
                        Log::warning($message->getSubject());
                        Log::warning("Some prompts are not ready yet. Skipping email processing for now.");
                        continue;
                    } */

                    // Ensure all collections are properly loaded
                    if (File::where('is_deleted', false)->get()->contains(fn(File $file) => !$file->is_embedded)) {
                        Log::warning($message->getSubject());
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

    private function getOrCreateUser(string $email): User
    {
        /** @var User $user */
        $user = User::where('email', $email)->first();
        if ($user) {
            Auth::login($user); // otherwise the tenant will not be properly set
        } else {

            /** @var Invitation $invitation */
            $invitation = Invitation::where('email', $email)->first();

            if (!$invitation) {
                $invitation = InvitationProxy::createInvitation($email, Str::before($email, '@'));
            }

            /** @var Tenant $tenant */
            $tenant = Tenant::create(['name' => Str::random()]);
            $user = $invitation->createUser([
                'password' => Str::random(64),
                'tenant_id' => $tenant->id,
                'type' => UserType::CLIENT(),
                'terms_accepted' => true,
            ]);

            $user->syncRoles(Role::ADMINISTRATOR, Role::LIMITED_ADMINISTRATOR, Role::BASIC_END_USER);

            Auth::login($user); // otherwise the tenant will not be properly set

            // Create prompts
            $this->importPrompt($user, 'default_assistant', 'seeds/prompts/default_assistant.txt');
            $this->importPrompt($user, 'default_chat', 'seeds/prompts/default_chat.txt');
            $this->importPrompt($user, 'default_chat_history', 'seeds/prompts/default_chat_history.txt');
            $this->importPrompt($user, 'default_debugger', 'seeds/prompts/default_debugger.txt');

            // Create shadow collections for some frameworks
            $frameworks = \App\Models\YnhFramework::all();

            foreach ($frameworks as $framework) {
                if ($framework->file === 'seeds/frameworks/anssi/anssi-genai-security-recommendations-1.0.jsonl') {
                    $this->importFramework($framework, 20);
                } else if ($framework->file === 'seeds/frameworks/anssi/anssi-guide-hygiene-detail.jsonl') {
                    $this->importFramework($framework, 10);
                } else if ($framework->file === 'seeds/frameworks/gdpr/gdpr.jsonl') {
                    $this->importFramework($framework, 30);
                } else if ($framework->file === 'seeds/frameworks/dora/dora.jsonl') {
                    $this->importFramework($framework, 50);
                } else if ($framework->file === 'seeds/frameworks/nis2/nis2-directive.jsonl') {
                    $this->importFramework($framework, 40);
                }
            }
        }
        return $user;
    }

    private function importPrompt(User $user, string $name, string $root)
    {
        $prompt = \Illuminate\Support\Facades\File::get(database_path($root));

        /** @var Prompt $p */
        $p = Prompt::where('created_by', $user->id)
            ->where('name', $name)
            ->first();

        if (isset($p)) {
            $p->update(['template' => $prompt]);
        } else {
            $p = Prompt::create([
                'created_by' => $user->id,
                'name' => $name,
                'template' => $prompt
            ]);
        }
    }

    private function importFramework(YnhFramework $framework, int $priority): void
    {
        $collection = $this->getOrCreateCollection($framework->collectionName(), $priority);
        if ($collection) {
            $url = \App\Http\Controllers\CyberBuddyController::saveLocalFile($collection, $framework->path());
        }
    }

    private function cyberBuddy(User $user, \Webklex\PHPIMAP\Message $message): void
    {
        // Extract the thread id in order to be able to load the existing conversation
        // If the thread id cannot be found, a new conversation is created
        $threadId = null;
        $matches = [];
        preg_match_all("/\s*thread_id=(?<threadid>[a-zA-Z0-9]{10})\s*/i", $message->getTextBody(), $matches, PREG_SET_ORDER);

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

        // Remove previous messages i.e. rows starting with >
        $body = trim(preg_replace("/^(>.*)|(On\s+.*\s+wrote:)[\n\r]?$/im", '', $message->getTextBody()));

        Log::debug('subject=' . $message->getSubject()[0] ?? '');
        Log::debug('body=' . $body);

        // Call CyberBuddy
        $request = new ConverseRequest();
        $request->replace([
            'thread_id' => $threadId,
            'directive' => $body,
        ]);

        $controller = new CyberBuddyNextGenController();
        $response = $controller->converse($request, true);
        $json = json_decode($response->content(), true);
        $subject = $message->getSubject()[0] ?? '';
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
            Log::error($message->getSubject());
            Log::error('Message could not be moved to the CyberBuddy folder!');
        }
    }

    private function memex(User $user, \Webklex\PHPIMAP\Message $message): void
    {
        $item = TimelineItem::createNote($user, $message->getTextBody(), $message->getSubject()[0] ?? '');
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
        if (!$message->move('Memex')) {
            Log::error($message->getSubject());
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
