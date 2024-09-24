<?php

namespace App\Modules\CyberBuddy\Http\Controllers;

use App\Models\YnhServer;
use App\Modules\AdversaryMeter\Http\Controllers\Controller;
use App\Modules\CyberBuddy\Helpers\ApiUtilsFacade as ApiUtils;
use App\User;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CyberBuddyController extends Controller
{
    public function __construct()
    {
        //
    }

    public function showPage()
    {
        return view('modules.cyber-buddy.page');
    }

    public function showChat()
    {
        return view('modules.cyber-buddy.chat');
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
        $botman->hears('/debug {question}', function (BotMan $botman, string $question) {

            $botman->types();
            $response = ApiUtils::ask_chunks_demo($question);
            if ($response['error']) {
                $botman->reply('Une erreur s\'est produite. Veuillez reposer votre question ultérieurement.');
            } else {

                $answer = $response['response'];
                $context = collect($response['context'] ?? []);
                $matches = array();
                $isOk = preg_match_all("/\[\[\d+]]/", $answer, $matches);

                if (!$isOk) {
                    $botman->reply($answer);
                } else {
                    /** @var array $refs */
                    $refs = $matches[0];
                    foreach ($refs as $ref) {
                        $id = Str::replace(['[', ']'], '', $ref);
                        $tooltip = $context->filter(fn($ctx) => $ctx['id'] === $id)->first();
                        if ($tooltip) {
                            $answer = Str::replace($ref, "
                              <div class=\"tooltip\" style=\"color:#f8b500\">[{$id}]
                                <span class=\"tooltiptext tooltip-right\">{$tooltip['text']}</span>
                              </div>
                            ", $answer);
                        }
                    }
                    $botman->reply($answer);
                }
            }
        });
        $botman->fallback(function (BotMan $botman) {
            $botman->reply('Désolé, je n\'ai pas compris votre commande.');
        });
        $botman->listen();
    }
}