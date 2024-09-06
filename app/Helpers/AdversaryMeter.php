<?php

namespace App\Helpers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdversaryMeter
{
    public static function redirectUrl()
    {
        $apiToken = self::findAnyAdversaryMeterApiToken(Auth::user()); // TODO : throw an error if not set ?
        $apiUrl = self::url();
        return asset('adversary_meter') . "/src/index.html?api_token={$apiToken}&api_url={$apiUrl}";
    }

    public static function addAsset(string $team, User $user, string $asset): array
    {
        // TODO : sink the CreateAsset event
        return self::addAsset2(self::apiKey(), $team, $user->email, $asset);
    }

    public static function removeAsset(string $team, User $user, string $asset): array
    {
        // TODO : sink the DeleteAsset event
        return self::removeAsset2(self::apiKey(), $team, $user->email, $asset);
    }

    public static function switchTeam(string $team, User $user): array
    {
        return self::switchTeam2($user->am_api_token, $team, $user->email);
    }

    private static function addAsset2(string $apiKey, string $team, string $user, string $asset): array
    {
        $endpointUrl = self::url() . '/api/v2/adversary/assets';
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
        ])->post($endpointUrl, [
            'team' => self::normalizeTeamName($team),
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
            'team' => self::normalizeTeamName($team),
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
            'team' => self::normalizeTeamName($team),
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

    private static function normalizeTeamName(string $team): string
    {
        return Str::replace(' ', '', Str::lower($team));
    }

    private static function findAnyAdversaryMeterApiToken(User $user): ?string
    {
        if ($user->am_api_token) {
            return $user->am_api_token;
        }

        $tenantId = $user->tenant_id;
        $customerId = $user->customer_id;

        if ($customerId) {

            // Find the first user of this customer with an API token
            $userTmp = User::where('customer_id', $customerId)
                ->where('tenant_id', $tenantId)
                ->whereNotNull('am_api_token')
                ->first();

            if ($userTmp) {
                return $userTmp->am_api_token;
            }
        }
        if ($tenantId) {

            // Find the first user of this tenant with an API token
            $userTmp = User::where('tenant_id', $tenantId)
                ->whereNotNull('am_api_token')
                ->first();

            if ($userTmp) {
                return $userTmp->am_api_token;
            }
        }

        $token = $user->createToken('adversarymeter', ['']);
        $plainTextToken = $token->plainTextToken;
        $token = $token?->accessToken;

        $user->am_api_token = $plainTextToken;
        $user->save();

        return $plainTextToken;
    }
}