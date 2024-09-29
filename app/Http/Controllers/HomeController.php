<?php

namespace App\Http\Controllers;

use App\Jobs\Summarize;
use App\Models\Invitation;
use App\Models\YnhNginxLogs;
use App\Models\YnhOrder;
use App\Models\YnhOsquery;
use App\Models\YnhOsqueryRule;
use App\Models\YnhServer;
use App\Models\YnhSshTraces;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'overview');
        $limit = $request->input('limit', 20);

        /** @var User $user */
        $user = Auth::user();

        // Disable a few tabs if Towerify is running as Cywise...
        $tab = $user->isCywiseUser() && ($tab === 'backups' || $tab === 'domains' || $tab === 'applications' || $tab === 'orders' || $tab === 'traces') ? 'overview' : $tab;
        $servers = YnhServer::forUser($user);
        $os_infos = YnhOsquery::osInfos($servers)
            ->map(function ($osInfos) {
                return (object)[
                    'ynh_server_id' => $osInfos->ynh_server_id,
                    'os' => "{$osInfos->os} {$osInfos->major_version}.{$osInfos->minor_version} ({$osInfos->architecture})",
                ];
            })
            ->groupBy('ynh_server_id');

        $memory_usage = collect();
        $disk_usage = collect();

        if ($tab === 'resources_usage') {
            $memory_usage = YnhOsquery::memoryUsage($servers)->groupBy('ynh_server_name');
            $disk_usage = YnhOsquery::diskUsage($servers)->groupBy('ynh_server_name');
        }

        $security_events = collect();

        if ($tab === 'security') {
            $security_events = [
                'authorized_keys' => YnhOsquery::authorizedKeys($servers, $limit),
                'kernel_modules' => YnhOsquery::kernelModules($servers, $limit),
                'suid_bin' => YnhOsquery::suidBinaries($servers, $limit),
                'last_logins_and_logouts' => YnhOsquery::loginsAndLogouts($servers, $limit),
                'users' => YnhOsquery::users($servers, $limit),
            ];
        }

        $security_rules = collect();

        if ($tab === 'security_rules') {
            $security_rules = YnhOsqueryRule::get();
        }

        $interdependencies = collect();

        if ($tab === 'interdependencies') {
            $interdependencies = YnhNginxLogs::interdependencies($servers);
        }

        $invitations = collect();

        if ($tab === 'invitations') {
            $invitations = Invitation::whereNull('user_id')->get();
        }

        $pendingActions = collect();
        $traces = collect();

        if ($tab === 'traces') {
            $pendingActions = $servers->flatMap(fn(YnhServer $server) => $server->pendingActions())
                ->sortBy([
                    fn(YnhSshTraces $a, YnhSshTraces $b) => $a->updated_at->diffInMilliseconds($b->updated_at),
                    fn(YnhSshTraces $a, YnhSshTraces $b) => strcmp($a->server->name, $b->server->name),
                ]);
            $traces = $servers->flatMap(fn(YnhServer $server) => $server->latestTraces())
                ->sortBy([
                    fn(YnhSshTraces $a, YnhSshTraces $b) => strcmp($a->server->name, $b->server->name),
                    fn(YnhSshTraces $a, YnhSshTraces $b) => $b->order - $a->order,
                ]);
        }

        $users = collect();

        if ($tab === 'users') {
            if ($user->tenant_id) {
                $users = User::where('is_active', true)->where('tenant_id', $user->tenant_id);
                if ($user->customer_id) {
                    $users = $users->where('customer_id', $user->customer_id);
                }
                $users = $users->get();
            } else {
                $users = User::where('is_active', true)->get();
            }
        }

        $domains = collect();

        if ($tab === 'domains') {
            $domains = $servers->flatMap(fn(YnhServer $server) => $server->domains);
        }

        $applications = collect();

        if ($tab === 'applications') {
            $applications = $servers->flatMap(fn(YnhServer $server) => $server->applications);
        }

        $backups = collect();

        if ($tab === 'backups') {
            $backups = $servers->flatMap(fn(YnhServer $server) => $server->backups);
        }

        $orders = collect();

        if ($tab === 'orders') {
            $orders = YnhOrder::forUser($user);
        }

        $overview = collect();

        if ($tab === 'overview') {
            $numberOfVulnerabilitiesByLevel = Summarize::numberOfVulnerabilitiesByLevel();
            $overview = [
                'servers_monitored' => Summarize::monitoredServers(),
                'ip_monitored' => Summarize::monitoredIps(),
                'dns_monitored' => Summarize::monitoredDns(),
                'metrics_collected' => Summarize::collectedMetrics($servers),
                'events_collected' => Summarize::collectedEvents($servers),
                'vulns_high' => $numberOfVulnerabilitiesByLevel['high'],
                'vulns_medium' => $numberOfVulnerabilitiesByLevel['medium'],
                'vulns_low' => $numberOfVulnerabilitiesByLevel['low'],
            ];
        }

        $notifications = $user->unreadNotifications
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'data' => $notification->data,
                    'timestamp' => $notification->updated_at->format('Y-m-d H:i') . ' UTC',
                ];
            })
            ->sortBy([
                ['timestamp', 'desc']
            ])
            ->values()
            ->all();

        return view('home.index', compact(
            'tab',
            'limit',
            'servers',
            'orders',
            'users',
            'invitations',
            'memory_usage',
            'disk_usage',
            'security_events',
            'security_rules',
            'interdependencies',
            'traces',
            'pendingActions',
            'domains',
            'applications',
            'backups',
            'os_infos',
            'notifications',
            'overview'
        ));
    }
}
