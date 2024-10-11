<?php

namespace App\View\Components;

use App\Models\YnhOsqueryRule;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class OsqueryRules extends Component
{
    public Collection $rules;

    public function __construct()
    {
        $this->rules = YnhOsqueryRule::get()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
    }

    public function render(): View|Closure|string
    {
        return view('components.osquery-rules');
    }
}
