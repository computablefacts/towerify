<?php

namespace App\Modules\CyberBuddy\Events;

use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportTable
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public array $credentials;
    public bool $updatable; // true iif the input directory must be monitored for updates
    public bool $copy; // true iif the data must be physically loaded in clickhouse server
    public bool $deduplicate;
    public string $table;
    public array $columns;
    public string $description;

    public function __construct(User $user, array $credentials, bool $copy, bool $deduplicate, bool $updatable, string $table, array $columns, string $description = '')
    {
        $this->user = $user;
        $this->credentials = $credentials;
        $this->updatable = $updatable;
        $this->copy = $copy;
        $this->deduplicate = $deduplicate;
        $this->table = $table;
        $this->columns = $columns;
        $this->description = $description;
    }
}
