<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Subscribed;
use App\Models\Role;
use App\Models\YnhServer;
use App\Modules\Reports\Helpers\ApiUtilsFacade as ApiUtils;
use App\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', Subscribed::class]);
    }

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $servers_type = $request->input('servers_type', '');
        $limit = $request->input('limit', 20);
        $tab = $request->input('tab', 'overview');

        if ($user->hasRole(Role::CYBERBUDDY_ONLY)) {
            if (!in_array($tab, ['ama', 'sca', 'frameworks', 'ai_writer', 'conversations', 'collections', 'documents', 'chunks', 'prompts'])) {
                return redirect()->route('home', ['tab' => 'ama']);
            }
        }

        // LEGACY CODE BEGINS : AUTOMATICALLY SYNC THE USER'S ACCOUNT ACROSS YNH SERVERS
        try {
            event(new PasswordReset($user));
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
        // LEGACY CODE ENDS
        // LEGACY CODE BEGINS : AUTOMATICALLY CREATE A PROPER SUPERSET ACCOUNT FOR ALL USERS
        try {
            if (!$user->superset_id) {

                $json = ApiUtils::get_or_add_user($user);

                $user->superset_id = $json['id'];
                $user->save();
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
        // LEGACY CODE ENDS

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
