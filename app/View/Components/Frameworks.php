<?php

namespace App\View\Components;

use App\Models\YnhFramework;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Frameworks extends Component
{
    public ?string $search;
    public Collection $highlights;
    public Collection $frameworks;

    public function __construct(?string $search = null)
    {
        $this->search = empty($search) || $search === 'null' ? null : $search;
        $this->frameworks = YnhFramework::query()
            ->orderBy('provider')
            ->orderBy('name')
            ->get();

        if (empty($this->search)) {
            $this->highlights = collect();
        } else {
            $this->highlights = $this->frameworks
                ->map(function (YnhFramework $framework) {
                    return (object)[
                        'framework' => $framework,
                        'highlights' => $framework->highlights(explode(' ', $this->search)),
                    ];
                })
                ->filter(fn(object $item) => count($item->highlights) > 0);
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.frameworks');
    }
}
