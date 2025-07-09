<?php

namespace App\Console\Commands;

use App\AgentSquad\Orchestrator;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FindLegalArguments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legal:find {input}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find legal arguments.';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $in = $this->argument('input');
        $input = $this->ask('Quelle est la thématique à développer ?');
        $user = new User();
        $messages = [];
        $orchestrator = new Orchestrator();
        $orchestrator->registerAgent(new LabourLawyer($in));

        while (true) {
            $answer = $orchestrator->run($user, "123abc", $messages, $input);
            $messages[] = [
                "role" => RoleEnum::USER->value,
                "content" => $input,
            ];
            $messages[] = [
                "role" => RoleEnum::ASSISTANT->value,
                "content" => $answer->markdown(),
            ];
            Log::debug($messages);
            $input = $this->ask($answer->markdown());
        }
    }
}
