<?php

namespace App\Events;

use App\Models\YnhApplication;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UninstallApp
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $uid;
    public User $user;
    public YnhApplication $application;

    public function __construct(string $uid, User $user, YnhApplication $application)
    {
        $this->uid = $uid;
        $this->user = $user;
        $this->application = $application;
    }
}
