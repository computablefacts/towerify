<?php

namespace App\Jobs;

use App\Models\YnhOsquery;
use App\Models\YnhOsqueryRule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Cleanup implements ShouldQueue
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
        // When a rule is disabled, cleanup the history
        $rules = YnhOsqueryRule::where('enabled', false)->get()->pluck('name');
        YnhOsquery::whereIn('name', $rules)->limit(10000)->delete();
    }
}
