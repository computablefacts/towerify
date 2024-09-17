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
                        return "<span data-json=\"{$json}\">{$server->name} / {$server->ip()}</a>";
                    })
                    ->join("<br>");
                $botman->reply($list);
            }
        });
        $botman->fallback(function (BotMan $botman) {
            $botman->reply('Désolé, je n\'ai pas compris votre commande.');
        });
        $botman->listen();
    }
}