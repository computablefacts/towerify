<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Enums\TextTypeEnum;
use App\Helpers\Agents\AbstractAction;
use App\Helpers\Agents\Agent;
use App\Helpers\OpenAi;
use App\Http\Requests\ConverseRequest;
use App\Jobs\ProcessIncomingEmails;
use App\Models\Conversation;
use App\Models\Prompt;
use App\Models\TimelineItem;
use App\Models\YnhServer;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Parsedown;

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

    public function converse(ConverseRequest $request, bool $fallbackOnNextCollection = true): JsonResponse
    {
        $threadId = Str::trim($request->string('thread_id', ''));
        $question = Str::trim($request->string('directive', ''));

        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized. Please log in and try again.',
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
        if (count($conversation->thread()) <= 0) {

            $timestamp = Carbon::now();

            // Load the prompt
            /** @var Prompt $prompt */
            $prompt = Prompt::where('created_by', $user->id)->where('name', 'default_assistant')->firstOrfail();
            $prompt->template = Str::replace('{DATE}', $timestamp->format('Y-m-d'), $prompt->template);
            $prompt->template = Str::replace('{TIME}', $timestamp->format('H:i'), $prompt->template);

            // Set a conversation-wide prompt
            $conversation->dom = json_encode(array_merge($conversation->thread(), [[
                'role' => RoleEnum::DEVELOPER->value,
                'content' => $prompt->template,
                'timestamp' => Carbon::now()->toIso8601ZuluString(),
            ]]));

            // Extract historical notes
            $notes = "# Notes\n\n" . TimelineItem::fetchNotes($user->id, null, null, 0)
                    ->map(function (TimelineItem $note) {
                        $attributes = $note->attributes();
                        return "## {$note->timestamp->format('Y-m-d H:i:s')}\n\n### {$attributes['subject']}\n\n{$attributes['body']}";
                    })
                    ->join("\n\n");

            if (!empty($notes)) {
                $conversation->dom = json_encode(array_merge($conversation->thread(), [[
                    'role' => RoleEnum::DEVELOPER->value,
                    'content' => $notes,
                    'timestamp' => Carbon::now()->toIso8601ZuluString(),
                ]]));
            }
        }

        $timestamp = Carbon::now();
        $thread = $conversation->thread();

        if (count($thread) > 0) {
            $last = end($thread);
            if (isset($last['timestamp'])) {
                $timestamp = Carbon::parse($last['timestamp']);
            }
        }

        // Transform URLs provided by the user to notes
        collect(ProcessIncomingEmails::extractAndSummarizeHyperlinks($question))->each(fn(array $summary) => TimelineItem::createNote($user, $summary['summary'], $summary['url']));

        // Extract newly created notes
        $notes = "# Notes\n\n" . TimelineItem::fetchNotes($user->id, $timestamp, null, 0)
                ->map(function (TimelineItem $note) {
                    $attributes = $note->attributes();
                    return "## {$note->timestamp->format('Y-m-d H:i:s')}\n\n### {$attributes['subject']}\n\n{$attributes['body']}";
                })
                ->join("\n\n");

        if (!empty($notes)) {
            $conversation->dom = json_encode(array_merge($conversation->thread(), [[
                'role' => RoleEnum::DEVELOPER->value,
                'content' => $notes,
                'timestamp' => Carbon::now()->toIso8601ZuluString(),
            ]]));
        }

        // Save the user's question
        $conversation->dom = json_encode(array_merge($conversation->thread(), [[
            'role' => RoleEnum::USER->value,
            'content' => $question,
            'timestamp' => Carbon::now()->toIso8601ZuluString(),
        ]]));
        $conversation->save();

        // Dispatch LLM call
        if (Str::startsWith($question, '/')) {
            $answer = $this->processCommand($user, $threadId, Str::after($question, '/'));
        } else {
            $answer = $this->processQuestion($user, $threadId, $conversation, $fallbackOnNextCollection);
        }

        // Save the LLM's answer
        $conversation->dom = json_encode(array_merge($conversation->thread(), [[
            'role' => RoleEnum::ASSISTANT->value,
            'answer' => $answer,
            'timestamp' => Carbon::now()->toIso8601ZuluString(),
        ]]));
        $conversation->save();

        // Summarize the beginning of the conversation
        if (empty($conversation->description)) {
            $exchange = collect($conversation->thread())
                ->filter(fn(array $message) => $message['role'] === RoleEnum::USER->value || $message['role'] === RoleEnum::ASSISTANT->value)
                ->take(4)
                ->map(function (array $message) {
                    if ($message['role'] === RoleEnum::USER->value) {
                        $msg = $message['content'] ?? '';
                    } else if ($message['role'] === RoleEnum::ASSISTANT->value) {
                        $msg = $message['answer']['raw_answer'] ?? '';
                    } else {
                        $msg = '';
                    }
                    return Str::upper($message['role']) . " : {$msg}";
                })
                ->join("\n\n");
            $response = OpenAi::execute("Summarize the conversation in about 10 words :\n\n{$exchange}");
            $conversation->description = $response['choices'][0]['message']['content'] ?? null;
            $conversation->save();
        }

        unset($answer['raw_answer']);

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

            $header = "
                <th class='left'>Name</th>
                <th class='left'>IP V4</th>
                <th class='left'>IP V6</th>
                <th class='left'>Domains</th>
                <th class='left'>Applications</th>
                <th class='left'>Users</th>
            ";

            $table = AbstractAction::htmlTable($header, $rows, 6);

            return [
                'response' => ['Here are the servers you have instrumented :'],
                'html' => $table,
                'raw_answer' => "Here are the servers you have instrumented :\n{$table}",
                'memoize' => false,
            ];
        }
        return [
            'response' => ['Sorry, I did not understand your request.'],
            'html' => '',
            'raw_answer' => 'Sorry, I did not understand your request.',
            'memoize' => false,
        ];
    }

    private function processQuestion(User $user, string $threadId, Conversation $conversation, bool $fallbackOnNextCollection = false): array
    {
        $agent = (new Agent($fallbackOnNextCollection))->run($conversation);
        $markdown = $agent->markdown();
        if ($agent->name() === 'query_knowledge_base' || $agent->name() === 'list_assets' ||
            $agent->name() === 'list_open_ports' || $agent->name() === 'list_vulnerabilities') {
            return [
                'response' => [],
                'html' => $agent->html(),
                'raw_answer' => $markdown,
                'memoize' => $agent->memoize(),
            ];
        }
        return [
            'response' => [],
            'html' => (new Parsedown)->text($markdown),
            'raw_answer' => $markdown,
            'memoize' => $agent->memoize(),
        ];
    }
}