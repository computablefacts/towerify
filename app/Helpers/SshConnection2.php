<?php

namespace App\Helpers;

use App\Enums\SshTraceStateEnum;
use App\Models\YnhServer;
use App\Models\YnhSshTraces;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SshConnection2
{
    private ?string $uid;
    private ?User $user;
    private YnhServer $server;

    public function __construct(YnhServer $server, ?string $uid, ?User $user)
    {
        $this->server = $server;
        $this->uid = $uid;
        $this->user = $user;
    }

    public function upload(string $filename, string $filecontent): bool
    {
        try {
            $sftp = $this->sshKeyPair()->newSftpConnection($this->ip(), $this->port(), $this->username());
            $this->newTrace(SshTraceStateEnum::IN_PROGRESS, "Uploading recipe '{$filename}'...");
            $isOk = $sftp->put($filename, $filecontent);
            $this->newTrace(SshTraceStateEnum::DONE, "Recipe uploaded.");
            $sftp->disconnect();
            return $isOk;
        } catch (\Exception $e) {
            Log::error($e);
            $this->newTrace(SshTraceStateEnum::ERRORED, "An error occurred.");
            return false;
        }
    }

    public function download(string $infile, string $outfile): bool
    {
        try {
            $sftp = $this->sshKeyPair()->newSftpConnection($this->ip(), $this->port(), $this->username());
            $this->newTrace(SshTraceStateEnum::IN_PROGRESS, "Downloading file '{$infile}'...");
            $isOk = $sftp->get($infile, $outfile);
            $this->newTrace(SshTraceStateEnum::DONE, "File downloaded.");
            $sftp->disconnect();
            return $isOk;
        } catch (\Exception $e) {
            Log::error($e);
            $this->newTrace(SshTraceStateEnum::ERRORED, "An error occurred.");
            return false;
        }
    }

    public function executeScript(string $script, bool $useSudo = false): bool
    {
        $output = [];
        $this->newTrace(SshTraceStateEnum::IN_PROGRESS, "Executing recipe '{$script}'...");
        $sudo = $useSudo ? 'sudo' : '';
        $cmd = "chmod +x {$script} && {$sudo} ./{$script} && rm {$script}";
        $isOk = $this->executeCommand($cmd, $output);
        if ($isOk) {
            $this->newTrace(SshTraceStateEnum::DONE, "Recipe executed.");
        } else {
            $this->newTrace(SshTraceStateEnum::ERRORED, "An error occurred.");
        }
        return $isOk;
    }

    public function executeCommand(string $command, array &$output): bool
    {
        try {
            $this->newTrace(SshTraceStateEnum::IN_PROGRESS, "Executing command '{$command}'...");
            $ssh = $this->sshKeyPair()->newSshConnection($this->ip(), $this->port(), $this->username());
            $ssh->setTimeout(1800); // Avoid timeout for long command/script. See: https://github.com/phpseclib/phpseclib/issues/1003#issuecomment-228605935
            $isOk = $ssh->exec($command, function ($str) use (&$output) {
                $output[] = trim($str);
            });
            if ($isOk) {
                $this->newTrace(SshTraceStateEnum::DONE, "Command executed.");
            } else {
                $this->newTrace(SshTraceStateEnum::ERRORED, "An error occurred.");
            }
            $errors = collect($ssh->getErrors())
                ->filter(fn($error) => !Str::contains($error, collect(['SSH_MSG_GLOBAL_REQUEST', 'SSH_MSG_DEBUG'])));
            if ($errors->count() > 0) {
                Log::error($ssh->getErrors());
                $this->newTrace(SshTraceStateEnum::ERRORED, "An error occurred.");
                return false;
            }
            $ssh->disconnect();
            return $isOk;
        } catch (\Exception $e) {
            Log::error($e);
            return false;
        }
    }

    public function newTrace(SshTraceStateEnum $state, string $trace): void
    {
        if ($this->uid) {

            $max = YnhSshTraces::where('uid', $this->uid)->max('order') ?? -1;
            $trace = preg_replace('/password=.*([&"])/', "password=*******$1", $trace);
            $trace = preg_replace('/-p \"[-a-zA-Z0-9^?!@#&\\\]+\"/', "-p '*******'", $trace);

            $sshTrace = new YnhSshTraces();
            $sshTrace->user_id = $this->user?->id;
            $sshTrace->uid = $this->uid;
            $sshTrace->order = $max + 1;
            $sshTrace->state = $state;
            $sshTrace->trace = Str::limit($trace, 512 - Str::length(' (truncated)'), ' (truncated)');
            $sshTrace->ynh_server_id = $this->server->id;
            $sshTrace->save();
        }
    }

    private function ip(): string
    {
        return $this->server->ip();
    }

    private function port(): int
    {
        return $this->server->ssh_port;
    }

    private function username(): string
    {
        return $this->server->ssh_username;
    }

    private function sshKeyPair(): SshKeyPair
    {
        return $this->server->sshKeyPair();
    }
}