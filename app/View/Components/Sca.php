<?php

namespace App\View\Components;

use App\Helpers\OssecRuleWindowsTestScript;
use App\Models\YnhOssecCheck;
use App\Models\YnhOssecPolicy;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

/**
 * SCA = Security Configuration Assessment
 */
class Sca extends Component
{
    public ?int $checkId;
    public ?string $policy;
    public ?string $framework;
    public ?string $search;
    public ?string $allChecksWindowsTestScript;
    public Collection $policies;
    public Collection $frameworks;
    public Collection $checks;

    public function __construct(?string $policy = null, ?string $framework = null, ?string $search = null, ?string $check = null)
    {
        $this->checkId = empty($check) || $check === 'null' ? null : (int)$check;
        $this->policy = empty($policy) || $policy === 'null' ? null : $policy;
        $this->framework = empty($framework) || $framework === 'null' ? null : $framework;
        $this->search = empty($search) || $search === 'null' ? null : $search;
        $this->policies = YnhOssecPolicy::select('id', 'uid', 'name')->orderBy('name')->get();
        $checks = YnhOssecCheck::query();

        if ($this->policy) {
            $checks = $checks->whereIn('ynh_ossec_policy_id', $this->policies->filter(fn(YnhOssecPolicy $p) => $p->uid === $this->policy)->pluck('id'));
        } else {
            $checks = $checks->whereRaw('1=0');
        }
        if ($this->search) {
            $checks = $checks->whereFullText(['title', 'description', 'rationale', 'remediation'], $this->search);
        }

        $this->frameworks = $checks->get()
            ->flatMap(fn(YnhOssecCheck $check) => $check->frameworks())
            ->unique()
            ->sort()
            ->values();
        $this->checks = $checks
            ->whereRaw($this->checkId ? "uid={$this->checkId}" : '1=1')
            ->get()
            ->filter(fn(YnhOssecCheck $check) => !$this->framework || in_array($framework, $check->frameworks()))
            ->sort(fn(YnhOssecCheck $check1, YnhOssecCheck $check2) => strcmp($check1->title, $check2->title));

        // TODO : when a check fails, display a link to the check infos /home?tab=sca&policy=<policy.uid>&rule=<check.uid>
        $this->allChecksWindowsTestScript = OssecRuleWindowsTestScript::begin() . "\n" .
            $this->checks->map(fn(YnhOssecCheck $check) => json_encode($check->requirements) . "\n")->join("\n") .
            OssecRuleWindowsTestScript::end();
    }

    public function render(): View|Closure|string
    {
        return view('components.sca');
    }
}
