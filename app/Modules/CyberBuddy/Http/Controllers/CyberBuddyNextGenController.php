<?php

namespace App\Modules\CyberBuddy\Http\Controllers;

use App\Models\YnhServer;
use App\Modules\AdversaryMeter\Http\Controllers\Controller;
use App\Modules\CyberBuddy\Enums\RoleEnum;
use App\Modules\CyberBuddy\Helpers\DeepInfra;
use App\Modules\CyberBuddy\Helpers\LlmFunctions\AbstractLlmFunction;
use App\Modules\CyberBuddy\Http\Requests\ConverseRequest;
use App\Modules\CyberBuddy\Models\Conversation;
use App\Modules\TheCyberBrief\Helpers\OpenAi;
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

    public function converse(ConverseRequest $request): JsonResponse
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

            // Set a conversation-wide prompt
            $conversation->dom = json_encode(array_merge($conversation->thread(), [[
                'role' => RoleEnum::DEVELOPER->value,
                'content' => "
                    When communicating with the user, follow these guidelines:
                    1. Be clear and concise in your explanations.
                    2. Avoid using jargon or technical terms.
                    3. Break down complex concepts into smaller, more manageable pieces.
                    4. Ask clarifying questions when the user's request is ambiguous.
                    5. Provide context for your explanations and decisions.
                    6. Be professional and respectful in all interactions.
                    7. Admit when you don't know something or are unsure.
                    8. Always return markdown.
                    9. Never talk about function calling.
                    10. Always respond in the user's language.

                    When answering the user's question, follow these guidelines:
                    1. Try to identify the theme of the question.
                    2. If the user's question is unrelated to cybersecurity, do not answer.
                    3. If the user's question is related to cybersecurity, use the query_issp function to answer it.
                    4. If the user's question is related to his assets, use the query_asset_database function to answer it.
                    5. If the user's question is related to his vulnerabilities, use the query_vulnerability_database function to answer it.
                    6. If the user's question is related to his open ports, use the query_open_port_database function to answer it.
                    7. If the user wants to begin monitoring an asset, use the begin_asset_monitoring function to do it.
                    8. If the user wants to end an asset monitoring, use the end_asset_monitoring function to do it.
                    9. If the user wants to remove an asset, use the remove_asset function to do it.
                    10. If the user wants to discover the subdomains of a given domain, use the discover_assets function to do it.
                ",
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
            $answer = $this->processQuestion($user, $threadId, $conversation);
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
                        $msg = collect($message['answer']['response'] ?? [])->join("\n\n");
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

            return [
                'response' => ['Here are the servers you have instrumented :'],
                'html' => AbstractLlmFunction::htmlTable($header, $rows, 6),
            ];
        }
        return [
            'response' => ['Sorry, I did not understand your request.'],
            'html' => '',
        ];
    }

    private function processQuestion(User $user, string $threadId, Conversation $conversation): array
    {
        $model = 'meta-llama/Meta-Llama-3-70B-Instruct';
        $temperature = 0.7;
        $tools = AbstractLlmFunction::schema();

        $messages = collect($conversation->thread())
            ->filter(fn(array $message) => $message['role'] === RoleEnum::USER->value || $message['role'] === RoleEnum::ASSISTANT->value || $message['role'] === RoleEnum::DEVELOPER->value)
            ->map(function (array $message) {
                if ($message['role'] === RoleEnum::USER->value || $message['role'] === RoleEnum::DEVELOPER->value) {
                    return [
                        // Map 'developer' to 'system' because DeepInfra is not up-to-date with the OpenAi API specification
                        // See https://deepinfra.com/docs/openai_api for details
                        'role' => $message['role'] === RoleEnum::DEVELOPER->value ? RoleEnum::SYSTEM->value : $message['role'],
                        'content' => $message['content'] ?? '',
                    ];
                }
                return [
                    'role' => RoleEnum::ASSISTANT->value,
                    'content' => collect($message['answer']['response'] ?? [])->join("\n"),
                ];
            })
            ->values()
            ->toArray();

        $response = DeepInfra::executeEx($messages, $model, $temperature, $tools);
        $toolCalls = $response['choices'][0]['message']['tool_calls'] ?? [];

        if (count($toolCalls) === 1) {
            $name = $toolCalls[0]['function']['name'] ?? '';
            $args = json_decode($toolCalls[0]['function']['arguments'], true) ?? [];
            $response = AbstractLlmFunction::handle($user, $threadId, $name, $args);
            $messages[] = [
                'role' => RoleEnum::TOOL->value,
                'tool_call_id' => $toolCalls[0]['id'],
                'content' => $response->text(),
            ];
            return [
                'messages' => $messages,
                'response' => [],
                'html' => $response->html(),
            ];
        }
        while (count($toolCalls) > 0) {
            $messages = array_merge($messages, $this->callTools($user, $threadId, $toolCalls));
            $response = DeepInfra::executeEx($messages, $model, $temperature, $tools);
            $toolCalls = $response['choices'][0]['message']['tool_calls'] ?? [];
        }
        return [
            'messages' => $messages,
            'response' => [],
            'html' => (new Parsedown)->text($response['choices'][0]['message']['content'] ?? ''),
        ];
    }

    private function callTools(User $user, string $threadId, array $tools): array
    {
        $messages = [];

        foreach ($tools as $tool) {
            $function = $tool['function'];
            $name = $function['name'] ?? '';
            $args = json_decode($function['arguments'], true) ?? [];
            $messages[] = [
                'role' => RoleEnum::TOOL->value,
                'tool_call_id' => $tool['id'],
                'content' => AbstractLlmFunction::handle($user, $threadId, $name, $args)->text(),
            ];
        }
        return $messages;
    }
}