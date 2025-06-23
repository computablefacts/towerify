<?php

namespace App\Helpers\Agents;

use App\Enums\RoleEnum;
use App\Helpers\DeepInfra;
use App\Models\Conversation;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Agent
{
    private bool $fallbackOnNextCollection;

    public function __construct(bool $fallbackOnNextCollection = false)
    {
        $this->fallbackOnNextCollection = $fallbackOnNextCollection;
    }

    public function run(Conversation $conversation): AbstractAction
    {
        $user = $conversation->createdBy();
        $threadId = $conversation->thread_id;
        $messages = $this->messages($conversation);
        try {
            if ($this->isIntentMalicious($user, $threadId, $messages)) {
                $action = new ClarifyRequest($user, $threadId, $messages, [], "Sorry! I cannot process this request.");
            } else {
                $action = $this->decideNextAction($user, $threadId, $messages);
            }
        } catch (\Exception $e) {
            $action = new ClarifyRequest($user, $threadId, $messages, [], "An error occurred, please try again later ({$e->getMessage()})");
        }
        return $action->execute();
    }

    protected function decideNextAction(User $user, string $threadId, array $messages): AbstractAction
    {
        $response = $this->llm($messages);
        $toolCalls = $response['choices'][0]['message']['tool_calls'] ?? [];

        if (count($toolCalls) === 0) {

            $answer = $response['choices'][0]['message']['content'] ?? '';
            // Log::debug("[0] answer : {$answer}");
            $answer = trim(preg_replace('/<think>.*?<\/think>/s', '', $answer));

            if (preg_match('/^<function=([a-zA-Z0-9_]+)>([{].*[}]).*/i', $answer, $matches)) {

                $name = trim($matches[1]);
                $args = json_decode(trim($matches[2]), true) ?? [];

                Log::warning("[1] $name(" . $matches[2] . ")");

                return $this->findTool($user, $threadId, $messages, $name, $args);
            }
            if (preg_match('/^[{]"function":"([a-zA-Z0-9_]+)","parameters":([{].*[}])[}].*/i', $answer, $matches)) {

                $name = trim($matches[1]);
                $args = json_decode(trim($matches[2]), true) ?? [];

                Log::warning("[2] $name(" . $matches[2] . ")");

                return $this->findTool($user, $threadId, $messages, $name, $args);
            }
            if (preg_match('/^\[?([a-zA-Z0-9_]+)\(question="(.*)"\)]?.*/i', $answer, $matches)) {

                $name = trim($matches[1]);
                $args = json_decode(trim($matches[2]), true) ?? [];

                Log::warning("[3] $name(" . $matches[2] . ")");

                return $this->findTool($user, $threadId, $messages, $name, $args);
            }
            return new ClarifyRequest($user, $threadId, $messages, [], $answer);
        }
        if (count($toolCalls) > 1) {
            return new ClarifyRequest($user, $threadId, $messages);
        }

        $name = $toolCalls[0]['function']['name'] ?? '';
        $args = json_decode($toolCalls[0]['function']['arguments'], true) ?? [];

        Log::debug("[4] $name(" . $toolCalls[0]['function']['arguments'] . ")");

        return $this->findTool($user, $threadId, $messages, $name, $args);
    }

    protected function llm(array $messages): array
    {
        return DeepInfra::executeEx($messages, 'meta-llama/Llama-4-Scout-17B-16E-Instruct', 0.7, $this->tools());
        // return DeepSeek::executeEx($messages, 'deepseek-chat', 0.7, $this->tools());
    }

    protected function tools(): array
    {
        return [
            BeginAssetMonitoring::schema(),
            // ClarifyRequest::schema(),
            DiscoverAssets::schema(),
            EndAssetMonitoring::schema(),
            ListAssets::schema(),
            QueryKnowledgeBase::schema(),
            ListOpenPorts::schema(),
            ListVulnerabilities::schema(),
            RemoveAsset::schema(),
            ScheduleTask::schema(),
        ];
    }

    protected function findTool(User $user, string $threadId, array $messages, string $name, array $args): AbstractAction
    {
        $args['fallback_on_next_collection'] = $this->fallbackOnNextCollection;
        return match ($name) {
            'begin_asset_monitoring' => new BeginAssetMonitoring($user, $threadId, $messages, $args),
            'discover_assets' => new DiscoverAssets($user, $threadId, $messages, $args),
            'end_asset_monitoring' => new EndAssetMonitoring($user, $threadId, $messages, $args),
            'list_assets' => new ListAssets($user, $threadId, $messages, $args),
            'list_open_ports' => new ListOpenPorts($user, $threadId, $messages, $args),
            'list_vulnerabilities' => new ListVulnerabilities($user, $threadId, $messages, $args),
            'query_knowledge_base' => new QueryKnowledgeBase($user, $threadId, $messages, $args),
            'remove_asset' => new RemoveAsset($user, $threadId, $messages, $args),
            'schedule_task' => new ScheduleTask($user, $threadId, $messages, $args),
            default => new ClarifyRequest($user, $threadId, $messages, $args),
        };
    }

    protected function messages(Conversation $conversation): array
    {
        return collect($conversation->thread())
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
                $memoize = !isset($message['answer']['memoize']) || $message['answer']['memoize'];
                return [
                    'role' => RoleEnum::ASSISTANT->value,
                    'content' => $memoize ? ($message['answer']['raw_answer'] ?? '') : 'This message has been hidden.',
                ];
            })
            ->map(fn(array $message) => [
                'role' => $message['role'],
                'content' => Str::before($message['content'], "\n\n**Sources:**\n"), // Remove sources. See QueryKnowledgeBase::enhanceXxxAnswerWithSources for details.
            ])
            ->values()
            ->toArray();
    }

    protected function isIntentMalicious(User $user, string $threadId, array $messages): bool
    {
        return Str::contains((end($messages) ?? [])['content'] ?? '', [
            "ignore previous instructions",
            "ignore above instructions",
            "disregard previous",
            "forget above",
            "system prompt",
            "new role",
            "act as",
            "ignore all previous commands"
        ]);
    }
}
