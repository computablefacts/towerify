<?php

namespace App\Listeners;

use App\Models\Prompt;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Konekt\User\Events\UserWasCreated;

class UserWasCreatedListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof UserWasCreated)) {
            throw new \Exception('Invalid event type!');
        }

        $user = $event->user;

        Auth::login($user); // otherwise the tenant will not be properly set

        // Set the user's prompts
        $this->importPrompt($user, 'default_assistant', 'seeds/prompts/default_assistant.txt');
        $this->importPrompt($user, 'default_chat', 'seeds/prompts/default_chat.txt');
        $this->importPrompt($user, 'default_chat_history', 'seeds/prompts/default_chat_history.txt');
        $this->importPrompt($user, 'default_debugger', 'seeds/prompts/default_debugger.txt');
    }

    private function importPrompt(User $user, string $name, string $root)
    {
        $prompt = File::get(database_path($root));

        /** @var Prompt $p */
        $p = Prompt::where('created_by', $user->id)
            ->where('name', $name)
            ->first();

        if (isset($p)) {
            if ($p->created_at->equalTo($p->updated_at)) {
                $p->update([
                    'template' => $prompt,
                ]);
            }
        } else {
            $p = Prompt::create([
                'created_by' => $user->id,
                'name' => $name,
                'template' => $prompt
            ]);
        }
    }
}
