<?php

namespace App\Http\Controllers;

use App\Models\YnhServer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'overview');
        $servers_type = $request->input('servers_type', '');
        $limit = $request->input('limit', 20);

        /** @var User $user */
        $user = Auth::user();
        $servers = YnhServer::forUser($user)
            ->filter(fn(YnhServer $server) => $servers_type !== 'ynh' || $server->isYunoHost());

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

        return view('index', compact(
            'tab',
            'limit',
            'servers',
            'servers_type',
            'notifications',
        ));
    }
}
