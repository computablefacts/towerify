<?php

namespace App\Modules\AdversaryMeter\Helpers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/** @deprecated */
class AdversaryMeter
{
    public static function redirectUrl()
    {
        $apiToken = self::findAnyAdversaryMeterApiToken(Auth::user()); // TODO : throw an error if not set ?
        $apiUrl = config('app.url');
        if (Str::endsWith($apiUrl, '/')) {
            $apiUrl = Str::beforeLast($apiUrl, '/');
        }
        return asset('adversary_meter') . "/src/index.html?api_token={$apiToken}&api_url={$apiUrl}";
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

        $user->am_api_token = $plainTextToken;
        $user->save();

        return $plainTextToken;
    }
}