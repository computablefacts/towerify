<?php

namespace App\Listeners;

use App\Models\Collection;
use App\Models\Prompt;
use App\Models\YnhFramework;
use App\Models\YnhServer;
use App\Rules\IsValidCollectionName;
use App\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PasswordResetListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof PasswordReset)) {
            throw new \Exception('Invalid event type!');
        }

        /** @var User $user */
        $user = $event->user;

        Auth::login($user); // otherwise the tenant will not be properly set

        // Set the user's prompts
        $this->importPrompt($user, 'default_assistant', 'seeds/prompts/default_assistant.txt');
        $this->importPrompt($user, 'default_chat', 'seeds/prompts/default_chat.txt');
        $this->importPrompt($user, 'default_chat_history', 'seeds/prompts/default_chat_history.txt');
        $this->importPrompt($user, 'default_debugger', 'seeds/prompts/default_debugger.txt');

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

        YnhServer::select('ynh_servers.*')
            ->join('ynh_users', 'ynh_users.ynh_server_id', '=', 'ynh_servers.id')
            ->where('ynh_users.email', $user->email)
            ->whereNotNull('ynh_servers.ip_address')
            ->whereNotNull('ynh_servers.ssh_port')
            ->whereNotNull('ynh_servers.ssh_username')
            ->whereNotNull('ynh_servers.ssh_public_key')
            ->whereNotNull('ynh_servers.ssh_private_key')
            ->where('ynh_servers.is_ready', true)
            ->where('ynh_servers.added_with_curl', false)
            ->where('ynh_servers.is_frozen', false)
            ->get()
            ->filter(fn(YnhServer $server) => $server->isYunoHost())
            ->filter(fn(YnhServer $server) => $server->sshTestConnection())
            ->each(function (YnhServer $server) use ($user) {
                $ssh = $server->sshConnection(Str::random(10), null);
                $server->sshCreateOrUpdateUserProfile($ssh, $user->name, $user->email, $user->ynhUsername(), $user->ynhPassword());
            });
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
