<?php

namespace App\Modules\Reports\Helpers;

use App\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiUtils
{
    public function get_or_add_user(User $user): ?array
    {
        $userSuperset = $this->user_exists($user->email);
        return $userSuperset ?: $this->create_user($user->ynhUsername(), $user->email, $user->ynhPassword());
    }

    private function create_user(string $username, string $email, string $password): ?array
    {
        $roles = $this->roles();
        $admin = collect($roles)->first(fn(array $role) => $role['name'] === 'Admin');
        $alpha = collect($roles)->first(fn(array $role) => $role['name'] === 'Alpha');
        $gamma = collect($roles)->first(fn(array $role) => $role['name'] === 'Gamma');
        $public = collect($roles)->first(fn(array $role) => $role['name'] === 'Public');
        $sqlLab = collect($roles)->first(fn(array $role) => $role['name'] === 'sql_lab');

        $tokens = $this->tokens();

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$tokens['access_token']}",
            'X-CSRFToken' => $tokens['csrf_token'],
        ])->post("{$this->api()}/security/users", [
            "username" => $username,
            "first_name" => $username,
            "last_name" => $username,
            "email" => $email,
            "password" => $password,
            "active" => true,
            "roles" => [$gamma['id']],
        ]);

        if ($response->successful()) {
            // $json = $response->json();
            // Log::debug($json);
            return $this->user_exists($email);
        }
        Log::error($response->body());
        return null;
    }

    private function roles(): array
    {
        $tokens = $this->tokens();

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$tokens['access_token']}",
            'X-CSRFToken' => $tokens['csrf_token'],
        ])->get("{$this->api()}/security/roles/");

        if ($response->successful()) {
            $json = $response->json();
            // Log::debug($json);
            return $json['result'] ?? [];
        }
        Log::error($response->body());
        return [];
    }

    private function user_exists(string $email): ?array
    {
        $tokens = $this->tokens();

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$tokens['access_token']}",
            'X-CSRFToken' => $tokens['csrf_token'],
        ])->get("{$this->api()}/security/users/?q=%28filters%3A%21%28%28col%3Aemail%2Copr%3Aeq%2Cvalue%3A%27{$email}%27%29%29%29");

        if ($response->successful()) {
            $json = $response->json();
            // Log::debug($json);
            return isset($json['result']) && count($json['result']) === 1 ? $json['result'][0] : null;
        }
        Log::error($response->body());
        return null;
    }

    private function tokens(): array
    {
        return $this->csrfToken();
    }

    private function csrfToken(): array
    {
        $login = $this->login();

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $login['access_token'],
        ])->get("{$this->api()}/security/csrf_token");

        if ($response->successful()) {
            $json = $response->json();
            // Log::debug($json);
            return [
                'access_token' => $login['access_token'],
                'csrf_token' => $json['result'],
            ];
        }
        Log::error($response->body());
        return [];
    }

    private function login(): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post("{$this->api()}/security/login", [
            'username' => config('towerify.reports.api_username'),
            'password' => config('towerify.reports.api_password'),
            'provider' => "db",
            'refresh' => true,
        ]);
        if ($response->successful()) {
            $json = $response->json();
            // Log::debug($json);
            return $json;
        }
        Log::error($response->body());
        return [];
    }

    private function api(): string
    {
        return config('towerify.reports.api');
    }
}