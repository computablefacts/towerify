<?php

namespace App\Modules\Federa\Jobs;

use App\Modules\Federa\Models\Collection;
use App\Modules\Federa\Models\CsvFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteCsvRows implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $maxExceptions = 1;
    public $timeout = 3 * 180; // 9mn

    public function __construct()
    {
        //
    }

    public function handle()
    {
        CsvFile::where('is_deleted', true)->delete();
        Collection::where('is_deleted', true)->delete();
    }
}
