<?php

namespace App\Events;

use App\Models\Tenant;
use App\Models\YnhServer;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RebuildPackagesList
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public ?YnhServer $server;

    public function __construct(User $user, ?YnhServer $server = null)
    {
        $this->user = $user;
        $this->server = $server;
    }

    public static function sink()
    {
        Tenant::all()
            ->map(fn(Tenant $tenant) => User::where('tenant_id', $tenant->id)->orderBy('created_at')->first())
            ->filter(fn(?User $user) => isset($user))
            ->each(fn(User $user) => RebuildPackagesList::dispatch($user));
    }
}
