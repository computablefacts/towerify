<?php

namespace App\Modules\CyberBuddy\Events;

use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IngestFile
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public string $collection;
    public int $fileId;

    public function __construct(User $user, string $collection, int $fileId)
    {
        $this->user = $user;
        $this->collection = $collection;
        $this->fileId = $fileId;
    }
}
