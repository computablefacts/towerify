<?php

namespace App\Modules\CyberBuddy\Http\Controllers;

use App\Models\YnhServer;
use App\Modules\AdversaryMeter\Http\Controllers\Controller;
use App\Modules\CyberBuddy\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\CyberBuddy\Http\Requests\ConverseRequest;
use App\Modules\CyberBuddy\Models\Conversation;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CyberBuddyNextGenController extends Controller
{
    public function __construct()
    {
        //
    }

    public function showAssistant()
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Conversation $conversation */
        $conversation = Conversation::create([
            'thread_id' => Str::random(10),
            'dom' => json_encode([]),
            'autosaved' => true,
            'created_by' => $user?->id,
            'format' => Conversation::FORMAT_V1,
        ]);
        return view('modules.cyber-buddy.assistant', ['threadId' => $conversation->thread_id]);
    }

    public function converse(ConverseRequest $request): JsonResponse
    {
        $threadId = Str::trim($request->string('thread_id', ''));
        $directive = Str::trim($request->string('directive', ''));
        $timestampIn = Carbon::now();

        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'error' => 'The directive cannot be processed.',
                'answer' => [
                    'response' => ['Sorry, you are not logged in. Please log in and try again.'],
                    'html' => '',
                ]]);
        }

        /** @var Conversation $conversation */
        $conversation = Conversation::where('thread_id', $threadId)
            ->where('format', Conversation::FORMAT_V1)
            ->where('created_by', $user->id)
            ->first();

        if (!$conversation) {
            return response()->json([
                'error' => "{$threadId} is an invalid thread id.",
                'answer' => [],
            ]);
        }
        if (Str::startsWith($directive, '/')) {
            $answer = $this->processCommand($user, $threadId, Str::after($directive, '/'));
        } else {
            $answer = $this->processDirective($user, $threadId, $directive);
        }

        $conversation->dom = json_encode(array_merge($conversation->thread(), [[
            'role' => 'user',
            'directive' => $directive,
            'timestamp' => $timestampIn->toIso8601ZuluString(),
        ], [
            'role' => 'bot',
            'answer' => $answer,
            'timestamp' => Carbon::now()->toIso8601ZuluString(),
        ]]));
        $conversation->save();

        return response()->json([
            'success' => 'The directive has been successfully processed.',
            'answer' => $answer,
        ]);
    }

    private function processCommand(User $user, string $threadId, string $command): array
    {
        if ($command === 'servers') {

            $rows = YnhServer::forUser($user)
                ->filter(fn(YnhServer $server) => $server->ip())
                ->map(function (YnhServer $server) use ($user) {
                    $name = $server->name;
                    $ipv4 = $server->ip();
                    $ipv6 = $server->ipv6() ?: '-';
                    $domains = $server->isYunoHost() ? $server->domains->count() : '-';
                    $applications = $server->isYunoHost() ? $server->applications->count() : '-';
                    $users = $server->isYunoHost() ? $server->users->count() : '-';
                    $linkServer = $server->isYunoHost() ?
                        '<a href="' . route('ynh.servers.edit', $server->id) . '" target="_blank">' . $name . '</a>' :
                        '<a href="' . route('home', ['tab' => 'servers', 'servers_type' => 'instrumented']) . '" target="_blank">' . $name . '</a>';
                    $linkDomains = $domains === '-' ? $domains : '<a href="' . route('ynh.servers.edit', $server->id) . "?tab=domains\" target=\"_blank\">$domains</a>";
                    $linkApplications = $applications === '-' ? $applications : '<a href="' . route('ynh.servers.edit', $server->id) . "?tab=applications\" target=\"_blank\">$applications</a>";
                    $linkUsers = $users === '-' ? $users : '<a href="' . route('ynh.servers.edit', $server->id) . "?tab=users\" target=\"_blank\">$users</a>";
                    return "
                      <tr>
                        <td class='left'>{$linkServer}</td>
                        <td class='left'>{$ipv4}</td>
                        <td class='left'>{$ipv6}</td>
                        <td class='left'>{$linkDomains}</td>
                        <td class='left'>{$linkApplications}</td>
                        <td class='left'>{$linkUsers}</td>
                      </tr>
                    ";
                })
                ->join("");

            $rows = empty($rows) ? "<tr><td colspan='6' style='text-align: center'>No data available.</td></tr>" : $rows;

            return [
                'response' => ['Here are the servers you have instrumented :'],
                'html' => "
                  <div class='tw-answer-table-wrapper'>
                    <div class='tw-answer-table'>
                      <table>
                        <thead>
                          <tr>
                            <th class='left'>Name</th>
                            <th class='left'>IP V4</th>
                            <th class='left'>IP V6</th>
                            <th class='left'>Domains</th>
                            <th class='left'>Applications</th>
                            <th class='left'>Users</th>
                          </tr>
                        </thead>
                        <tbody>
                          {$rows}
                        </tbody>
                      </table>
                    </div>
                  </div>
                ",
            ];
        }
        return [
            'response' => ['Sorry, I did not understand your request.'],
            'html' => '',
        ];
    }

    private function processDirective(User $user, string $threadId, string $directive): array
    {

        $directive = htmlspecialchars($directive, ENT_QUOTES, 'UTF-8');
        $response = ApiUtils::chat_manual_demo($threadId, null, $directive);

        if ($response['error']) {
            return [
                'response' => ['Sorry, an error occurred. Please try again later.'],
                'html' => '',
            ];
        }

        $answer = CyberBuddyController::enhanceAnswerWithSources($response['response'], collect($response['context'] ?? []));

        return [
            'response' => [],
            'html' => $answer,
        ];
    }
}