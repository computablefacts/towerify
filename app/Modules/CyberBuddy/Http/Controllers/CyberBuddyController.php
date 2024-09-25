<?php

namespace App\Modules\CyberBuddy\Http\Controllers;

use App\Models\YnhServer;
use App\Modules\AdversaryMeter\Http\Controllers\Controller;
use App\Modules\CyberBuddy\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\CyberBuddy\Http\Conversations\FrameworksConversation;
use App\User;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CyberBuddyController extends Controller
{
    public static function enhanceAnswerWithSources(string $answer, Collection $sources): string
    {
        $matches = [];
        $isOk = preg_match_all("/\[\[\d+]]/", $answer, $matches);
        if (!$isOk) {
            return Str::replace(["\n\n", "\n-"], "<br>", $answer);
        }
        /** @var array $refs */
        $refs = $matches[0];
        foreach ($refs as $ref) {
            $id = Str::replace(['[', ']'], '', $ref);
            $tooltip = $sources->filter(fn($ctx) => $ctx['id'] === $id)->first();
            if ($tooltip) {
                $answer = Str::replace($ref, "
                  <div class=\"tooltip\">
                    <b style=\"color:#f8b500\">[{$id}]</b>
                    <span class=\"tooltiptext tooltip-top\">{$tooltip['text']}</span>
                  </div>
                ", $answer);
            }
        }
        return Str::replace(["\n\n", "\n-"], "<br>", $answer);
    }

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
        $botman = app('botman');
        $botman->hears('/login {username} {password}', function (BotMan $botman, string $username, string $password) {
            $user = $this->user($botman);
            if ($user) {
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
        })->skipsConversation();
        $botman->hears('/servers', function (BotMan $botman) {
            $user = $this->user($botman);
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
                        $linkServer = '<a href="' . route('ynh.servers.edit', $server->id) . '" target="_blank">' . $name . '</a>';
                        $linkDomains = $domains === '-' ? $domains : '<a href="' . route('ynh.servers.edit', $server->id) . "?tab=domains\" target=\"_blank\">$domains</a>";
                        $linkApplications = $applications === '-' ? $applications : '<a href="' . route('ynh.servers.edit', $server->id) . "?tab=applications\" target=\"_blank\">$applications</a>";
                        $linkUsers = $users === '-' ? $users : '<a href="' . route('ynh.servers.edit', $server->id) . "?tab=users\" target=\"_blank\">$users</a>";
                        return "
                          <tr data-json=\"{$json}\">
                            <td>{$linkServer}</td>
                            <td>{$os}</td>
                            <td>{$ipv4}</td>
                            <td>{$ipv6}</td>
                            <td>{$linkDomains}</td>
                            <td>{$linkApplications}</td>
                            <td>{$linkUsers}</td>
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
                      </thead>
                      <tbody>
                        {$list}
                      </tbody>
                    </table>
                ");
            }
        })->skipsConversation();
        $botman->hears('/question {question}', function (BotMan $botman, string $question) {
            $botman->types();
            $response = ApiUtils::ask_chunks_demo($question);
            if ($response['error']) {
                $botman->reply('Une erreur s\'est produite. Veuillez réessayer ultérieurement.');
            } else {
                $answer = self::enhanceAnswerWithSources($response['response'], collect($response['context'] ?? []));
                $botman->reply($answer);
            }
        })->skipsConversation();
        $botman->hears('/frameworks', fn(BotMan $botman) => $botman->startConversation(new FrameworksConversation()));
        $botman->fallback(fn(BotMan $botman) => $botman->reply('Désolé, je n\'ai pas compris votre commande.'));
        $botman->listen();
    }

    private function user(BotMan $botman): ?User
    {
        /** @var int $userId */
        $userId = $botman->userStorage()->get('user_id');
        if ($userId) {
            return User::find($userId);
        }
        /** @var User $user */
        $user = Auth::user();
        if ($user) {
            $botman->userStorage()->save(['user_id' => $user->id]);
        }
        return $user;
    }
}