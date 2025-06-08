<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportVirtualTable
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public string $table;
    public string $query;
    public string $description;

    public function __construct(User $user, string $table, string $query, string $description = '')
    {
        $this->user = $user;
        $this->table = $table;
        $this->query = $query;
        $this->description = $description;
    }
}
