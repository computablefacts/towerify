<?php

namespace App\Helpers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdversaryMeter
{
    public static function redirectUrl()
    {
        $apiToken = Auth::user()->am_api_token; // TODO : throw an error if not set ?
        $apiUrl = self::url();
        return asset('adversary_meter') . "/src/index.html?api_token={$apiToken}&api_url={$apiUrl}";
    }

    public static function addAsset(string $client, User $user, string $asset): array
    {
        return self::addAsset2(self::apiKey(), $client, $user->email, $asset);
    }

    public static function removeAsset(string $client, User $user, string $asset): array
    {
        return self::removeAsset2(self::apiKey(), $client, $user->email, $asset);
    }

    public static function switchTeam(string $client, User $user): array
    {
        return self::switchTeam2($user->am_api_token, $client, $user->email);
    }

    private static function addAsset2(string $apiKey, string $team, string $user, string $asset): array
    {
        $endpointUrl = self::url() . '/api/v2/adversary/assets';
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
        ])->post($endpointUrl, [
            'team' => $team,
            'username' => $user,
            'asset' => $asset,
        ]);
        if ($response->successful()) {
            $json = $response->json();
            // Log::debug($json);
            return $json;
        }
        Log::error($response->body());
        return [];
    }

    private static function removeAsset2(string $apiKey, string $team, string $user, string $asset): array
    {
        $endpoint = self::url() . '/api/v2/adversary/assets';
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->delete($endpoint, [
            'team' => $team,
            'username' => $user,
            'asset' => $asset,
        ]);
        if ($response->successful()) {
            $json = $response->json();
            // Log::debug($json);
            return $json;
        }
        Log::error($response->body());
        return [];
    }

    private static function switchTeam2(string $apiKey, string $team, string $user)
    {
        $endpoint = self::url() . '/api/v3/users/switch-team';
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            'team' => $team,
            'username' => $user,
        ]);
        if ($response->successful()) {
            $json = $response->json();
            // Log::debug($json);
            return $json;
        }
        Log::error($response->body());
        return [];
    }

    private static function url(): string
    {
        return config('towerify.adversarymeter.url');
    }

    private static function apiKey(): string
    {
        return config('towerify.adversarymeter.api_key');
    }
}