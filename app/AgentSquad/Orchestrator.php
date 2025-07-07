<?php

namespace App\AgentSquad;

use App\Enums\RoleEnum;
use App\Helpers\LlmProvider;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Orchestrator
{
    private LlmProvider $llmProvider;
    private string $model;
    /** @var AbstractAction[] $agents */
    private array $agents = [];
    /** @var AbstractAction[] $commands */
    private array $commands = [];

    public function __construct(string $model = 'meta-llama/Llama-4-Scout-17B-16E-Instruct')
    {
        $this->llmProvider = new LlmProvider(LlmProvider::DEEP_INFRA);
        $this->model = $model;
    }

    public function registerAgent(AbstractAction $agent): void
    {
        $this->agents[$agent->name()] = $agent;
    }

    public function unregisterAgent(string $name): void
    {
        unset($this->agents[$name]);
    }

    public function registerCommand(AbstractAction $command): void
    {
        $this->commands[$command->name()] = $command;
    }

    public function unregisterCommand(string $name): void
    {
        unset($this->commands[$name]);
    }

    public function run(User $user, string $threadId, array $messages, string $input): Answer
    {
        try {
            $input = Str::trim($input);
            if (Str::startsWith($input, '/')) {
                return $this->processCommand($user, $threadId, $messages, Str::after($input, '/'));
            }
            return $this->processInput($user, $threadId, $messages, $input);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return new Answer("Sorry, an error occurred: {$e->getMessage()}", false);
        }
    }

    private function processCommand(User $user, string $threadId, array $messages, string $command): Answer
    {
        if (!isset($this->commands[$command])) {
            return new Answer("Sorry, I did not find your command: {$command}", false);
        }
        return $this->commands[$command]->execute($user, $threadId, $messages, $command);
    }

    private function processInput(User $user, string $threadId, array $messages, string $input, array $chainOfThought = [], int $depth = 0): Answer
    {
        if ($depth >= 3) {
            Log::error("Too many iterations: $depth");
            Log::error("message: " . json_encode($messages));
            Log::error("chain-of-thought: " . json_encode($chainOfThought));
            /** @var ThoughtActionObservation $cot */
            $cot = array_pop($chainOfThought);
            return new Answer($cot->observation(), false);
        }

        $template = '{"thought":"describe here succinctly your thoughts about the question you have been asked", "action_name":"set here the name of the action to execute", "action_input":"set here the input for the action"}';
        $ctx = implode("\n", array_map(fn(ThoughtActionObservation $tao) => "> Thought: {$tao->thought()}\n> Observation: {$tao->observation()}", $chainOfThought));
        $actions = implode("\n", array_map(fn(AbstractAction $action) => "- {$action->name()}: {$action->description()}", $this->agents));
        $prompt = "
You are an expert intent classifier.
You will use the chain-of-thought provided (between [COT] and [/COT]) and the user's input (between [INPUT] and [/INPUT]) to understand the user's intent and select the appropriate action (between [ACTIONS] and [/ACTIONS]).
You will rewrite the input for the action so that the action can efficiently be executed.

Your guidelines:
- Sometimes you might have to use multiple actions to solve the user's task. You have to do that in a loop.
- The original user input could have multiple tasks, you will use your chain-of-thought to understand the previous actions taken and the next steps you should take.
- Read your chain-of-thought, take your time to understand it, see if there were many tasks and if you executed them all.
- If the user's intent is not clear, then make the action 'clarify_request' with a clarifying question as 'input'.
- If there are no actions to be taken, then make the action 'respond_to_user' with your final thoughts combining all previous responses as 'input'.
- As soon as your chain-of-thought provides enough information to answer something to the user, you should respond with 'respond_to_user'.
- Respond with 'respond_to_user' only when there are no actions to select from or there is no next action to take.
- Ensure the action's input are in the same language as the user's input.
- Always return a valid JSON like {$template} and nothing else.

Your current chain-of-thought (between [COT] and [/COT]) to help you plan the next action to execute:
[COT]
{$ctx}
[/COT]

Your available actions (between [ACTIONS] and [/ACTIONS]) are:
[ACTIONS]
{$actions}
[/ACTIONS]

The user's input (between [INPUT] and [/INPUT]) is:
[INPUT]
{$input}
[/INPUT]
        ";

        // Log::debug($prompt);

        $messages[] = [
            'role' => RoleEnum::USER->value,
            'content' => $prompt,
        ];
        $response = $this->callLlm($messages);
        array_pop($messages);
        $answer = $response['choices'][0]['message']['content'] ?? '';
        $answer = preg_replace('/<think>.*?<\/think>/s', '', $answer);
        $json = json_decode(Str::trim($answer), true);

        // Log::debug($answer);

        if (!isset($json)) {

            $json = [];
            $matches = null;

            if (preg_match('/"thought"\s*:\s*"(.*)"/is', $answer, $matches)) {
                $json['thought'] = $matches[1];
            }
            if (preg_match('/"action_name"\s*:\s*"([a-z0-9_]+)"/is', $answer, $matches)) {
                $json['action_name'] = $matches[1];
            }
            if (preg_match('/"action_input"\s*:\s*"(.*)"/is', $answer, $matches)) {
                $json['action_input'] = $matches[1];
            }
        }
        if (empty($json)) {
            return new Answer("Invalid JSON response: {$answer}", false);
        }
        if (!isset($json['thought'])) {
            return new Answer("The thought is missing: {$answer}", false);
        }
        if (!isset($json['action_name'])) {
            return new Answer("The action name is missing: {$answer}", false);
        }
        if (!isset($json['action_input'])) {
            return new Answer("The action input is missing: {$answer}", false);
        }
        if ($json['action_name'] === 'respond_to_user') {
            return new Answer($json['action_input']);
        }
        if ($json['action_name'] === 'clarify_request') {
            return new Answer($json['action_input']);
        }
        if (!isset($this->agents[$json['action_name']])) {
            return new Answer("The action is unknown: {$answer}", false);
        }

        $answer = $this->agents[$json['action_name']]->execute($user, $threadId, $messages, $json['action_input']);

        if ($answer->failure()) {
            return $answer;
        }

        $chainOfThought[] = new ThoughtActionObservation($json['thought'], "{$json['action_name']}[{$json['action_input']}]", $answer->markdown());
        return $this->processInput($user, $threadId, $messages, $input, $chainOfThought, $depth + 1);
    }

    private function callLlm(array $messages): array
    {
        return $this->llmProvider->execute($messages, $this->model);
    }
}