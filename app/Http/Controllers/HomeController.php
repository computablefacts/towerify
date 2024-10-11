<?php

namespace App\Http\Controllers;

use App\Jobs\Summarize;
use App\Models\Invitation;
use App\Models\YnhOsqueryRule;
use App\Models\YnhServer;
use App\Modules\CyberBuddy\Http\Controllers\CyberBuddyController;
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

        $security_rules = collect();

        if ($tab === 'security_rules') {
            $security_rules = YnhOsqueryRule::get();
        }

        $invitations = collect();

        if ($tab === 'invitations') {
            $invitations = Invitation::whereNull('user_id')->get();
        }

        $applications = collect();

        if ($tab === 'applications') {
            $applications = $servers->flatMap(fn(YnhServer $server) => $server->applications);
        }

        $knowledge_base = collect();

        if ($tab === 'knowledge_base') {
            $knowledge_base = (new CyberBuddyController())->files();
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
            'invitations',
            'security_rules',
            'applications',
            'notifications',
            'knowledge_base'
        ));
    }
}
