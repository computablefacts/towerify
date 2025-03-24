<?php

namespace App\Modules\CyberBuddy\Http\Controllers;

use App\Models\YnhServer;
use App\Modules\AdversaryMeter\Http\Controllers\Controller;
use App\Modules\CyberBuddy\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\CyberBuddy\Http\Requests\ConverseRequest;
use App\Modules\CyberBuddy\Models\Chunk;
use App\Modules\CyberBuddy\Models\Conversation;
use App\Modules\CyberBuddy\Models\File;
use App\Modules\TheCyberBrief\Helpers\OpenAi;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CyberBuddyNextGenController extends Controller
{
    public function __construct()
    {
        //
    }

    public function showAssistant(Request $request)
    {
        $conversationId = $request->query('conversation_id');

        /** @var User $user */
        $user = Auth::user();

        if ($conversationId) {
            $conversation = Conversation::where('id', $conversationId)
                ->where('format', Conversation::FORMAT_V1)
                ->where('created_by', $user?->id)
                ->first();
        }

        /** @var Conversation $conversation */
        $conversation = $conversation ?? Conversation::create([
            'thread_id' => Str::random(10),
            'dom' => json_encode([]),
            'autosaved' => true,
            'created_by' => $user?->id,
            'format' => Conversation::FORMAT_V1,
        ]);

        $threadId = $conversation->thread_id;

        return view('modules.cyber-buddy.assistant', ['threadId' => $threadId]);
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

        if (empty($conversation->description)) {
            $exchange = collect($conversation->thread())
                ->take(6)
                ->map(function ($exchange) {
                    if ($exchange['role'] === 'user') {
                        $messages = $exchange['directive'];
                    } else if ($exchange['role'] === 'bot') {
                        $messages = collect($exchange['answer']['response'] ?? [])->join("\n\n");
                    } else {
                        $messages = '';
                    }
                    return Str::upper($exchange['role']) . " : {$messages}";
                })
                ->join("\n\n");
            $response = OpenAi::summarize("Condense the following conversation into about 10 words :\n\n{$exchange}");
            $conversation->description = $response['choices'][0]['message']['content'] ?? null;
        }

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
        return [
            'response' => [],
            'html' => $this->enhanceAnswerWithSources($response['response'], collect($response['context'] ?? [])),
        ];
    }

    private function enhanceAnswerWithSources(string $answer, Collection $sources): string
    {
        $matches = [];
        // Extract: [12] from [[12]] or [[12] and [13]] from [[12],[13]]
        $isOk = preg_match_all("/\[\[\d+]]|\[\[\d+]|\[\d+]]/", $answer, $matches);
        if (!$isOk) {
            return Str::replace(["\n\n", "\n-"], "<br>", $answer);
        }
        $references = [];
        /** @var array $refs */
        $refs = $matches[0];
        foreach ($refs as $ref) {
            $id = Str::replace(['[', ']'], '', $ref);
            /** @var array $tooltip */
            $tooltip = $sources->filter(fn($ctx) => $ctx['id'] == $id)->first();
            /** @var Chunk $chunk */
            $chunk = Chunk::find($id);
            /** @var File $file */
            $file = $chunk?->file()->first();
            $src = $file ? "<a href=\"{$file->downloadUrl()}\" style=\"text-decoration:none;color:black\">{$file->name_normalized}.{$file->extension}</a>, p. {$chunk->page}" : "";
            if ($tooltip) {
                if (Str::startsWith($tooltip['text'], 'ESSENTIAL DIRECTIVE')) {
                    $color = '#1DD288';
                } else if (Str::startsWith($tooltip['text'], 'STANDARD DIRECTIVE')) {
                    $color = '#C5C3C3';
                } else if (Str::startsWith($tooltip['text'], 'ADVANCED DIRECTIVE')) {
                    $color = '#FDC99D';
                } else {
                    $color = '#F8B500';
                }
                $answer = Str::replace($ref, "<b style=\"color:{$color}\">[{$id}]</b>", $answer);
                $references[$id] = "
                  <li style=\"padding:0;margin-bottom:0.25rem\">
                    <b style=\"color:{$color}\">[{$id}]</b>&nbsp;
                    <div class=\"cb-tooltip-list\">
                      {$src}
                      <span class=\"cb-tooltiptext cb-tooltip-list-top\" style=\"background-color:{$color};color:#444;\">
                        {$tooltip['text']}
                      </span>
                    </div>
                  </li>
                ";
            }
        }
        ksort($references);
        $answer = "{$answer}<br><br><b>Sources :</b><ul>" . collect($references)->values()->join("") . "</ul>";
        return Str::replace(["\n\n", "\n-"], "<br>", $answer);
    }
}