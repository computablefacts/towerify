<?php

namespace App\View\Components;

use App\Models\YnhOsquery;
use App\Models\YnhServer;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Events extends Component
{
    public Collection $entries;

    public function __construct(YnhServer $server)
    {
        $this->entries = YnhOsquery::suspiciousEvents(collect([$server]), Carbon::now()->subDays(60))
            ->map(fn(array $entry) => (object)$entry);
    }

    public function render(): View|Closure|string
    {
        return view('cywise.components.events');
    }
}
