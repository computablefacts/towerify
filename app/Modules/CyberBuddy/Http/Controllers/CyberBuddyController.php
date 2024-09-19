<?php

namespace App\Modules\CyberBuddy\Http\Controllers;

use App\Models\YnhServer;
use App\Modules\AdversaryMeter\Http\Controllers\Controller;
use App\User;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Auth;

class CyberBuddyController extends Controller
{
    public function __construct()
    {
        //
    }

    public function showPage()
    {
        return view('cyber-buddy.page');
    }

    public function showChat()
    {
        return view('cyber-buddy.chat');
    }

    public function handle(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $botman = app('botman');
        $botman->hears('/login {username} {password}', function (BotMan $botman, string $username, string $password) use ($user) {
            if ($user) {
                $botman->userStorage()->save(['user_id' => $user->id]);
                $botman->reply('You are now logged in.');
            } else {
                $user = User::where('email', $username)->first();
                if (!$user) {
                    $botman->reply('Invalid username or password.');
                } else {
                    if (Auth::attempt(['email' => $username, 'password' => $password])) {
                        $botman->userStorage()->save(['user_id' => $user->id]);
                        $botman->reply('You are now logged in.');
                    } else {
                        $botman->reply('Invalid username or password.');
                    }
                }
            }
        });
        $botman->hears('/servers', function (BotMan $botman) {
            /** @var int $userId */
            $userId = $botman->userStorage()->get('user_id');
            $user = $userId ? User::find($userId) : null;
            $servers = $user ? YnhServer::forUser($user) : collect();
            if ($servers->isEmpty()) {
                $botman->reply('Connectez-vous pour accéder à cette commande.<br>Pour ce faire, vous pouvez utiliser la commande <b>/login {username} {password}</b>');
            } else {
                $list = $servers->filter(fn(YnhServer $server) => $server->ip())
                    ->map(function (YnhServer $server) use ($botman, $user) {
                        $json = base64_encode(json_encode($server));
                        $name = $server->name;
                        $os = isset($os_infos[$server->id]) && $os_infos[$server->id]->count() >= 1 ? $os_infos[$server->id][0]->os : '-';
                        $ipv4 = $server->ip();
                        $ipv6 = $server->isFrozen() || $server->ipv6() === '<unavailable>' ? '-' : $server->ipv6();
                        $domains = $server->isFrozen() || $server->addedWithCurl() ? '-' : $server->domains->count();
                        $applications = $server->isFrozen() || $server->addedWithCurl() ? '-' : $server->applications->count();
                        $users = $server->isFrozen() || $server->addedWithCurl() ? '-' : $server->users->count();
                        return "
                          <tr data-json=\"{$json}\">
                            <td><div class=\"tooltip\">{$name}<span class=\"tooltiptext tooltip-right\">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</span></div></td>
                            <td>{$os}</td>
                            <td>{$ipv4}</td>
                            <td>{$ipv6}</td>
                            <td>{$domains}</td>
                            <td>{$applications}</td>
                            <td>{$users}</td>
                          </tr>
                        ";
                    })
                    ->join("");
                $botman->reply("
                    <table data-type=\"table\" style=\"width:100%\">
                      <thead>
                          <tr>
                            <th>Name</th>
                            <th>OS</th>
                            <th>IP V4</th>
                            <th>IP V6</th>
                            <th>Domains</th>
                            <th>Applications</th>
                            <th>Users</th>
                          </tr>
                          <tbody>
                            {$list}
                          </tbody>
                      </thead>
                    </table>
                ");
            }
        });
        $botman->fallback(function (BotMan $botman) {
            $botman->reply('Désolé, je n\'ai pas compris votre commande.');
        });
        $botman->listen();
    }
}