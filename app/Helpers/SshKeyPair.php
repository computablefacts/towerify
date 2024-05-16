<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;

class SshKeyPair
{
    private string $publickey;
    private string $privatekey;

    public function init(): void
    {
        $keys = RSA::createKey(4096);
        $this->init2((string)$keys->getPublicKey(), (string)$keys);
    }

    public function init2(string $publickey, string $privatekey): void
    {
        $this->publickey = $publickey;
        $this->privatekey = $privatekey;
    }

    public function publicKey(): string
    {
        return $this->publickey;
    }

    public function privateKey(): string
    {
        return $this->privatekey;
    }

    public function authorizedKey(): string
    {
        return PublicKeyLoader::load($this->publicKey())->toString('OpenSSH');
    }

    public function echoAuthorizedKey(): string
    {
        return "echo \"{$this->authorizedKey()}\" >>~/.ssh/authorized_keys";
    }

    public function isSshConnectionUpAndRunning(string $ip, int $port, string $username): bool
    {
        try {
            $ssh = $this->newSshConnection($ip, $port, $username);
            return true;
        } catch (\Exception $e) {
            Log::error($e);
        }
        return false;
    }

    public function newSshConnection(string $ip, int $port, string $username): SSH2
    {
        $privatekey = PublicKeyLoader::loadPrivateKey($this->privateKey());
        $ssh = new SSH2($ip, $port);
        if (!$ssh->login($username, $privatekey)) {
            throw new \Exception("SSH login failed! (ip={$ip}, port={$port}, username={$username})");
        }
        return $ssh;
    }

    public function newSftpConnection(string $ip, int $port, string $username): SFTP
    {
        $privatekey = PublicKeyLoader::loadPrivateKey($this->privateKey());
        $sftp = new SFTP($ip, $port);
        if (!$sftp->login($username, $privatekey)) {
            throw new \Exception("SFTP login failed! (ip={$ip}, port={$port}, username={$username})");
        }
        return $sftp;
    }
}