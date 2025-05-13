<?php

namespace App\Listeners;

use App\Models\Collection;
use App\Models\Prompt;
use App\Models\Role;
use App\Models\YnhFramework;
use App\Rules\IsValidCollectionName;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
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

                // Create shadow collections for some frameworks
                $frameworks = \App\Models\YnhFramework::all();

                foreach ($frameworks as $framework) {
                    if ($framework->file === 'seeds/frameworks/anssi/anssi-genai-security-recommendations-1.0.jsonl') {
                        $this->importFramework($framework, 20);
                    } else if ($framework->file === 'seeds/frameworks/anssi/anssi-guide-hygiene-detail.jsonl') {
                        $this->importFramework($framework, 10);
                    } else if ($framework->file === 'seeds/frameworks/gdpr/gdpr.jsonl') {
                        $this->importFramework($framework, 30);
                    } else if ($framework->file === 'seeds/frameworks/dora/dora.jsonl') {
                        $this->importFramework($framework, 50);
                    } else if ($framework->file === 'seeds/frameworks/nis2/nis2-directive.jsonl') {
                        $this->importFramework($framework, 40);
                    }
                }
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

    private function importFramework(YnhFramework $framework, int $priority): void
    {
        $collection = $this->getOrCreateCollection($framework->collectionName(), $priority);
        if ($collection && $collection->files()->count() === 0) {
            $url = \App\Http\Controllers\CyberBuddyController::saveLocalFile($collection, $framework->path());
        }
    }

    private function getOrCreateCollection(string $collectionName, int $priority): ?Collection
    {
        /** @var \App\Models\Collection $collection */
        $collection = Collection::where('name', $collectionName)
            ->where('is_deleted', false)
            ->first();
        if (!$collection) {
            if (!IsValidCollectionName::test($collectionName)) {
                Log::error("Invalid collection name : {$collectionName}");
                return null;
            }
            $collection = Collection::create([
                'name' => $collectionName,
                'priority' => max($priority, 0),
            ]);
        }
        return $collection;
    }
}
