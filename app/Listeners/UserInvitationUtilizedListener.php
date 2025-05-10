<?php

namespace App\Listeners;

use App\Models\Prompt;
use App\Models\Role;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Konekt\User\Events\UserInvitationUtilized;

class UserInvitationUtilizedListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof UserInvitationUtilized)) {
            throw new \Exception('Invalid event type!');
        }

        $invitation = $event->getInvitation();
        /** @var User $created */
        $created = User::where('id', $invitation->user_id)->first();

        if ($created) {

            /** @var User $creator */
            $creator = User::where('id', $invitation->created_by)->first();

            if ($creator) {

                Auth::login($creator); // otherwise the tenant will not be properly set

                // Set tenant
                if ($creator->tenant_id) {
                    $created->tenant_id = $creator->tenant_id;
                }

                // Set customer
                if ($creator->customer_id) {
                    $created->customer_id = $creator->customer_id;
                }

                // Add the 'cyberbuddy only' role to some user
                if ($created->tenant_id === 18 && $created->customer_id === 9) {

                    $cyberBuddyEndUser = Role::where('name', Role::CYBERBUDDY_ONLY)->first();

                    if ($cyberBuddyEndUser) {
                        $created->roles()->syncWithoutDetaching($cyberBuddyEndUser);
                    }
                } else {

                    // Add the 'basic end user' role to the user
                    $basicEndUser = Role::where('name', Role::BASIC_END_USER)->first();

                    if ($basicEndUser) {
                        $created->roles()->syncWithoutDetaching($basicEndUser);
                    }

                    // Add the 'limited administrator' role to the user
                    $limitedAdministrator = Role::where('name', Role::LIMITED_ADMINISTRATOR)->first();

                    if ($limitedAdministrator) {
                        $created->roles()->syncWithoutDetaching($limitedAdministrator);
                    }
                }
                $created->save();

                Auth::logout();
                Auth::login($created); // otherwise the tenant will not be properly set

                // Set the user's prompts
                $this->importPrompt($created, 'default_assistant', 'seeds/prompts/default_assistant.txt');
                $this->importPrompt($created, 'default_chat', 'seeds/prompts/default_chat.txt');
                $this->importPrompt($created, 'default_chat_history', 'seeds/prompts/default_chat_history.txt');
                $this->importPrompt($created, 'default_debugger', 'seeds/prompts/default_debugger.txt');
            }
        }
    }

    private function importPrompt(User $user, string $name, string $root)
    {
        $prompt = File::get(database_path($root));

        /** @var Prompt $p */
        $p = Prompt::where('created_by', $user->id)
            ->where('name', $name)
            ->first();

        if (isset($p)) {
            $p->update(['template' => $prompt]);
        } else {
            $p = Prompt::create([
                'created_by' => $user->id,
                'name' => $name,
                'template' => $prompt
            ]);
        }
    }
}
