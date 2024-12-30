<?php

namespace App\View\Components;

use App\Models\YnhOssecCheck;
use App\Models\YnhOssecPolicy;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Sca extends Component
{
    public ?string $policy;
    public ?string $framework;
    public Collection $policies;
    public Collection $frameworks;
    public Collection $checks;

    public function __construct(?string $policy = null, ?string $framework = null)
    {
        $this->policies = YnhOssecPolicy::select('id', 'uid', 'name')->orderBy('name')->get();

        $checks = YnhOssecCheck::query();

        if (!empty($policy)) {
            $checks = $checks->whereIn('ynh_ossec_policy_id', $this->policies->filter(fn(YnhOssecPolicy $p) => $p->uid === $policy)->pluck('id'));
        }

        $this->policy = empty($policy) ? null : $policy;
        $this->framework = empty($framework) || $framework === 'null' ? null : $framework;
        $this->frameworks = $checks->get()
            ->flatMap(fn(YnhOssecCheck $check) => $check->frameworks())
            ->unique()
            ->sort()
            ->values();
        $this->checks = $checks->get()
            ->filter(fn(YnhOssecCheck $check) => $this->framework || in_array($framework, $check->frameworks()))
            ->sort(fn(YnhOssecCheck $check1, YnhOssecCheck $check2) => strcmp($check1->title, $check2->title));
    }

    public function render(): View|Closure|string
    {
        return view('components.sca');
    }
}
