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
    public string $region;
    public string $accessKeyId;
    public string $secretAccessKey;
    public string $inputFolder;
    public string $outputFolder;
    public bool $copy; // true iif the data must be physically loaded in clickhouse server
    public bool $deduplicate;
    public string $table;
    public array $columns;
    public string $description;

    public function __construct(User $user, string $region, string $accessKeyId, string $secretAccessKey, string $inputFolder, string $outputFolder, bool $copy, bool $deduplicate, string $table, array $columns, string $description = '')
    {
        $this->user = $user;
        $this->region = $region;
        $this->accessKeyId = $accessKeyId;
        $this->secretAccessKey = $secretAccessKey;
        $this->inputFolder = $inputFolder;
        $this->outputFolder = $outputFolder;
        $this->copy = $copy;
        $this->deduplicate = $deduplicate;
        $this->table = $table;
        $this->columns = $columns;
        $this->description = $description;
    }
}
