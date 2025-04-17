<?php

namespace App\Modules\CyberBuddy\Jobs;

use App\Hashing\TwHasher;
use App\Modules\CyberBuddy\Http\Controllers\CyberBuddyNextGenController;
use App\Modules\CyberBuddy\Http\Requests\ConverseRequest;
use App\Modules\CyberBuddy\Models\Conversation;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Message;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Webklex\IMAP\Facades\Client;

class ProcessIncomingEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const string SENDER = 'cyberbuddy@cywise.io';

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

                    if (!collect($to)->contains(self::SENDER)) {
                        continue;
                    }
                    if (count($from) !== 1) {
                        Log::error($message->getSubject());
                        Log::error('Message from multiple addresses!');
                        continue;
                    }

                    /** @var \Webklex\PHPIMAP\Address $address */
                    $address = $from[0];
                    /** @var User $user */
                    $user = User::where('email', /* config('towerify.admin.email') */ $address->mail)->first();

                    if (!$user) {
                        Log::error("Unknown user: {$address->mail}");
                        continue;
                    }

                    Auth::login($user);

                    Log::debug('seq=' . $message->getSequence());
                    Log::debug('uid=' . $message->getUid());
                    Log::debug('subject=' . $message->getSubject()->all()[0]);
                    Log::debug('body=' . $message->getTextBody());

                    $threadId = Str::limit(TwHasher::hash("{$address->mail}-{$message->getUid()}"), 10, '');

                    /** @var Conversation $conversation */
                    $conversation = Conversation::where('thread_id', $threadId)
                        ->where('format', Conversation::FORMAT_V1)
                        ->where('created_by', $user?->id)
                        ->first();

                    $conversation = $conversation ?? Conversation::create([
                        'thread_id' => $threadId,
                        'dom' => json_encode([]),
                        'autosaved' => true,
                        'created_by' => $user?->id,
                        'format' => Conversation::FORMAT_V1,
                    ]);

                    $request = new ConverseRequest();
                    $request->replace([
                        'thread_id' => $threadId,
                        'directive' => $message->getTextBody(),
                    ]);

                    $controller = new CyberBuddyNextGenController();
                    $response = $controller->converse($request);
                    $json = json_decode($response->content(), true);
                    $subject = $message->getSubject()[0];
                    $body = $json['answer']['html'] ?? '';

                    Mail::html("{$body}\n\nthread_id={$threadId}", function (Message $msg) use ($message, $address, $subject) {
                        $msg->to($address->mail)->from(self::SENDER)->subject("Re: {$subject}");
                    });

                    if (!$message->move('CyberBuddy')) {
                        Log::error($message->getSubject());
                        Log::error('Message could not be moved!');
                    }
                }
            }

            $client->disconnect();

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
