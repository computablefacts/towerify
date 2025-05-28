<?php

namespace App\Jobs;

use App\Http\Controllers\CyberBuddyNextGenController;
use App\Http\Requests\ConverseRequest;
use App\Listeners\EndVulnsScanListener;
use App\Models\Conversation;
use App\Models\ScheduledTask;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RunScheduledTasks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $maxExceptions = 1;
    public $timeout = 3 * 180; // 9mn

    public function __construct()
    {
        //
    }

    public function handle()
    {
        ScheduledTask::where('next_run_date', '<=', Carbon::now())
            ->get()
            ->each(function (ScheduledTask $task) {
                try {

                    $threadId = Str::random(10);
                    $conversation = Conversation::create([
                        'thread_id' => $threadId,
                        'dom' => json_encode([]),
                        'autosaved' => true,
                        'created_by' => $task->createdBy()->id,
                        'format' => Conversation::FORMAT_V1,
                    ]);
                    $request = new ConverseRequest([
                        'thread_id' => $threadId,
                        'directive' => $task->task,
                    ]);
                    $response = (new CyberBuddyNextGenController())->converse($request, true);
                    $json = json_decode($response->content(), true);
                    $subject = $task->name;
                    $body = $json['answer']['html'] ?? '';

                    Log::debug($json);

                    EndVulnsScanListener::sendEmail(
                        ProcessIncomingEmails::SENDER_CYBERBUDDY,
                        $task->createdBy()->email,
                        $subject,
                        "",
                        $body
                    );

                    $task->prev_run_date = Carbon::now();
                    $task->next_run_date = Carbon::instance($task->cron()->getNextRunDate());
                    $task->save();

                } catch (\Exception $exception) {
                    Log::error($exception->getMessage());
                }
            });
    }
}
