<?php

namespace App\Http\Controllers;

use App\Enums\SshTraceStateEnum;
use App\Events\AddTwrUserPermission;
use App\Events\AddUserPermission;
use App\Events\ConfigureHost;
use App\Events\CreateBackup;
use App\Events\InstallApp;
use App\Events\PullServerInfos;
use App\Events\RemoveUserPermission;
use App\Events\UninstallApp;
use App\Helpers\Messages;
use App\Helpers\SshKeyPair;
use App\Http\Requests\AddUserPermissionRequest;
use App\Http\Requests\ConfigureHostRequest;
use App\Http\Requests\CreateBackupRequest;
use App\Http\Requests\CreateHostRequest;
use App\Http\Requests\DownloadBackupRequest;
use App\Http\Requests\ExecuteShellCommandRequest;
use App\Http\Requests\InstallAppRequest;
use App\Http\Requests\LoadMessagesRequest;
use App\Http\Requests\PullServerInfosRequest;
use App\Http\Requests\RemoveHostRequest;
use App\Http\Requests\RemoveUserPermissionRequest;
use App\Http\Requests\TestSshConnectionRequest;
use App\Http\Requests\UninstallAppRequest;
use App\Models\YnhApplication;
use App\Models\YnhBackup;
use App\Models\YnhDomain;
use App\Models\YnhOrder;
use App\Models\YnhServer;
use App\Models\YnhUser;
use App\Modules\AdversaryMeter\Events\CreateAsset;
use App\Modules\AdversaryMeter\Events\DeleteAsset;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class YnhServerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(YnhServer $server, Request $request)
    {
        $tab = $request->input('tab', 'events');
        $limit = $request->input('limit', 40);

        return view('_server', compact(
            'tab',
            'limit',
            'server',
        ));
    }

    public function testSshConnection(YnhServer $server, TestSshConnectionRequest $request)
    {
        if ($server->isFrozen()) {
            return response()->json(['error' => "The server configuration is frozen."]);
        }

        $isOk = $server->sshKeyPair()->isSshConnectionUpAndRunning($request->ip, $request->port, $request->username);

        if ($isOk) {
            return response()->json(['success' => 'Connection succeeded.']);
        }
        return response()->json(['error' => 'Connection failed!']);
    }

    public function configure(YnhServer $server, ConfigureHostRequest $request)
    {
        if ($server->isFrozen()) {
            return response()->json(['error' => "The server configuration is frozen."]);
        }
        if ($server->isReady()) {
            return response()->json(['error' => "The server has already been setup. Please, contact the support for more informations."]);
        }

        $principal = $server->domain();

        if ($principal && $principal->name !== $request->domain) {
            return response()->json(['error' => "{$principal->name} is already a principal domain."]);
        }

        $domain = trim($request->domain);
        $process = new Process(['dig', '+short', "*.{$domain}"]);
        $process->run();

        if (!$process->isSuccessful()) {
            $cmd = $process->getCommandLine();
            $output = $process->getErrorOutput();
            Log::error("{$cmd} : {$output}");
            return response()->json(['error' => "The 'dig' command is unavailable."]);
        }

        $ip = trim($process->getOutput());

        if (!$ip) {
            $cmd = $process->getCommandLine();
            $output = $process->getOutput();
            Log::error("{$cmd} : {$output}");
            return response()->json(['error' => "The DNS record for {$request->domain} is not ready yet. Please, wait for DNS propagation and try again."]);
        }
        if ($request->ip !== $ip) {
            return response()->json(['error' => "IP mismatch: {$request->domain}'s IP address is {$ip} and not {$request->ip}."]);
        }
        if (!$server->sshKeyPair()->isSshConnectionUpAndRunning($request->ip, $request->port, $request->username)) {
            return response()->json(['error' => "SSH connection failed!"]);
        }

        $server->name = $request->name;
        $server->ip_address = $request->ip;
        $server->ssh_port = $request->port;
        $server->ssh_username = $request->username;
        $server->save();

        CreateAsset::dispatch($server->user()->first(), $server->ip(), true, [$server->name]);

        /** @var User $user */
        $user = Auth::user();

        if (!$principal) {
            $server->domains()->save(YnhDomain::updateOrCreate([
                'ynh_server_id' => $server->id,
                'name' => $request->domain,
            ], [
                'name' => $request->domain,
                'is_principal' => true,
                'ynh_server_id' => $server->id,
                'updated' => false,
            ]));
            CreateAsset::dispatch($server->user()->first(), $request->domain, true, [$server->name]);
        }

        $uid = Str::random(10);
        $ssh = $server->sshConnection($uid, $user);
        $ssh->newTrace(SshTraceStateEnum::PENDING, "Your host is being configured!");

        ConfigureHost::dispatch($uid, $user, $server);

        return response()->json(['success' => "Your host is being configured!"]);
    }

    public function create(CreateHostRequest $request)
    {
        $orderId = $request->input('order', 0);
        $server = null;

        if ($orderId > 0) {
            $server = YnhServer::where('ynh_order_id', $orderId)->first();
        }
        if (!$server) {

            $keys = new SshKeyPair();
            $keys->init();

            $server = YnhServer::create([
                'name' => '',
                'user_id' => Auth::user()->id,
                'ssh_public_key' => $keys->publicKey(),
                'ssh_private_key' => $keys->privateKey(),
                'ynh_order_id' => $orderId === 0 ? null : $orderId,
                'secret' => Str::random(30),
            ]);

            $server->name = "YNH{$server->id}";
            $server->save();
        }

        $tab = 'settings';
        return view('_server', compact('server', 'tab'));
    }

    public function delete(YnhServer $server, RemoveHostRequest $request)
    {
        if ($server->isFrozen()) {
            return response()->json(['error' => "The server configuration is frozen."]);
        }

        $uid = Str::random(10);
        $ssh = $server->sshConnection($uid, Auth::user());
        $ssh->newTrace(SshTraceStateEnum::PENDING, "The server is being removed from the inventory!");

        if ($server->ip()) {

            DeleteAsset::dispatch($server->user()->first(), $server->ip());

            $ssh->newTrace(SshTraceStateEnum::IN_PROGRESS, 'Stopping asset monitoring...');
            DeleteAsset::dispatch(Auth::user(), $server->ip());
            $ssh->newTrace(SshTraceStateEnum::DONE, 'Asset monitoring stopped.');

            $server->sshEnableAdminConsole($ssh);

            // TODO : remove fail2ban's whitelisted IPs
            // TODO : re-open closed ports
            // See ConfigureHostListener for details
        }

        $server->delete();

        return response()->json(['success' => "The server has been removed from the inventory!"]);
    }

    public function uninstallApp(YnhServer $server, YnhApplication $application, UninstallAppRequest $request)
    {
        if ($server->isFrozen()) {
            return response()->json(['error' => "The server configuration is frozen."]);
        }

        $uid = Str::random(10);
        $ssh = $server->sshConnection($uid, Auth::user());
        $ssh->newTrace(SshTraceStateEnum::PENDING, "Your application is being removed!");

        UninstallApp::dispatch($uid, Auth::user(), $application);

        return response()->json(['success' => "Your application is being removed!"]);
    }

    public function installApp(YnhServer $server, YnhOrder $ynhOrder, InstallAppRequest $request)
    {
        if ($server->isFrozen()) {
            return response()->json(['error' => "The server configuration is frozen."]);
        }

        $order = $ynhOrder;

        if (!$server->isReady()) {
            return response()->json(['error' => "The server is not ready yet! Try again in a moment."]);
        }

        $domain = $server->domains->where('path', "{$order->sku()}.{$server->domain()->name}")->first();

        if ($domain) {
            return response()->json(['error' => "{$domain} is already in use."]);
        }

        $uid = Str::random(10);
        $ssh = $server->sshConnection($uid, Auth::user());
        $ssh->newTrace(SshTraceStateEnum::PENDING, "Your application is being installed!");

        InstallApp::dispatch($uid, Auth::user(), $server, $order);

        return response()->json(['success' => "Your application is being installed!"]);
    }

    public function addTwrUserPermission(YnhServer $server, User $user, string $perm, AddUserPermissionRequest $request)
    {
        if ($server->isFrozen()) {
            return response()->json(['error' => "The server configuration is frozen."]);
        }

        // TODO : sanity checks

        $uid = Str::random(10);
        $ssh = $server->sshConnection($uid, Auth::user());
        $ssh->newTrace(SshTraceStateEnum::PENDING, "The user's permission is being added!");

        AddTwrUserPermission::dispatch($uid, Auth::user(), $server, $user, $perm);

        return response()->json(['success' => "The user's permission is being added!"]);
    }

    public function addUserPermission(YnhServer $server, YnhUser $ynhUser, string $perm, AddUserPermissionRequest $request)
    {
        if ($server->isFrozen()) {
            return response()->json(['error' => "The server configuration is frozen."]);
        }

        $user = $ynhUser;

        // TODO : sanity checks

        $uid = Str::random(10);
        $ssh = $server->sshConnection($uid, Auth::user());
        $ssh->newTrace(SshTraceStateEnum::PENDING, "The user's permission is being added!");

        AddUserPermission::dispatch($uid, Auth::user(), $server, $user, $perm);

        return response()->json(['success' => "The user's permission is being added!"]);
    }

    public function removeUserPermission(YnhServer $server, YnhUser $ynhUser, string $perm, RemoveUserPermissionRequest $request)
    {
        if ($server->isFrozen()) {
            return response()->json(['error' => "The server configuration is frozen."]);
        }

        $user = $ynhUser;

        // TODO : sanity checks

        $uid = Str::random(10);
        $ssh = $server->sshConnection($uid, Auth::user());
        $ssh->newTrace(SshTraceStateEnum::PENDING, "The user's permission is being removed!");

        RemoveUserPermission::dispatch($uid, Auth::user(), $server, $user, $perm);

        return response()->json(['success' => "The user's permission is being removed!"]);
    }

    public function pullServerInfos(YnhServer $server, PullServerInfosRequest $request)
    {
        if ($server->isFrozen()) {
            return response()->json(['error' => "The server configuration is frozen."]);
        }

        $uid = Str::random(10);
        $ssh = $server->sshConnection($uid, Auth::user());
        $ssh->newTrace(SshTraceStateEnum::PENDING, "The server infos are being pulled!");

        PullServerInfos::dispatch($uid, Auth::user(), $server);

        return response()->json(['success' => "The server infos are being pulled!"]);
    }

    public function createBackup(YnhServer $server, CreateBackupRequest $request)
    {
        if ($server->isFrozen()) {
            return response()->json(['error' => "The server configuration is frozen."]);
        }

        $uid = Str::random(10);
        $ssh = $server->sshConnection($uid, Auth::user());
        $ssh->newTrace(SshTraceStateEnum::PENDING, "The server backup is being created!");

        CreateBackup::dispatch($uid, Auth::user(), $server);

        return response()->json(['success' => "The server backup is being created!"]);
    }

    public function downloadBackup(YnhServer $server, YnhBackup $backup, DownloadBackupRequest $request)
    {
        if ($server->isFrozen()) {
            return response()->json(['error' => "The server configuration is frozen."]);
        }

        $serverTenantId = $server->user->tenant_id;
        $serverCustomerId = $server->user->customer_id;

        $backupTenantId = $backup->user->tenant_id;
        $backupCustomerId = $backup->user->customer_id;

        $userTenantId = Auth::user()->tenant_id;
        $userCustomerId = Auth::user()->customer_id;

        if ($userTenantId) {
            if ($serverTenantId != $backupTenantId || $userTenantId != $backupTenantId) {
                return response()->json(['error' => 'Not allowed.'], 401);
            }
        }
        if ($userCustomerId) {
            if ($serverCustomerId != $backupCustomerId || $userCustomerId != $backupCustomerId) {
                return response()->json(['error' => 'Not allowed.'], 401);
            }
        }
        if (!$backup->storage_path) {
            return response()->json(['error' => 'Missing storage path.'], 500);
        }

        $path = $backup->storage_path;

        if (!Storage::disk(config('filesystems.backups'))->exists($path)) {
            return response()->json(['error' => 'Missing file.'], 404);
        }

        $filename = Str::afterLast($path, '/');
        $filesize = Storage::disk(config('filesystems.backups'))->size($path);
        $filetype = Storage::disk(config('filesystems.backups'))->mimeType($path);
        $headers = [
            'Content-Type' => $filetype,
            'Content-Length' => $filesize,
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ];

        // See https://aws.amazon.com/fr/blogs/developer/amazon-s3-php-stream-wrapper/
        $env = config('app.env');
        $bucket = config('filesystems.disks.backups.bucket');
        $url = "s3://{$bucket}/{$env}/{$path}";
        $client = Storage::disk('s3')->getClient();
        $client->registerStreamWrapper();

        return response()->streamDownload(function () use ($url) {
            if (!($stream = fopen($url, 'r'))) {
                throw new \Exception("Could not open stream: {$url}");
            }
            while (!feof($stream)) {
                echo fread($stream, 1024);
            }
            fclose($stream);
        }, $filename, $headers);
    }

    public function executeShellCommand(YnhServer $server, ExecuteShellCommandRequest $request)
    {
        if ($server->isFrozen()) {
            return response()->json(['error' => "The server configuration is frozen."]);
        }

        $cmd = $request->get('cmd');
        $uid = Str::random(10);
        $ssh = $server->sshConnection($uid, Auth::user());
        $ssh->newTrace(SshTraceStateEnum::PENDING, "Executing shell command...");

        $output = [];

        if ($ssh->executeCommand($cmd, $output)) {
            $ssh->newTrace(SshTraceStateEnum::DONE, "Shell command executed.");
            $str = trim(collect($output)->join(''));
            try {
                return response()->json([
                    'success' => Str::of($str)
                        ->split('/[\n\r]+/')
                        ->map(fn(string $row) => trim($row))
                        ->filter(fn(string $row) => $row && $row !== '')
                ]);
            } catch (\Exception $e) {
                Log::error($e);
            }
        }
        return response()->json(['error' => 'An error occurred'], 500);
    }

    public function messages(YnhServer $server, LoadMessagesRequest $request)
    {
        return response()->json(Messages::get(collect([$server]), Carbon::now()->subDays(10)), 200);
    }
}