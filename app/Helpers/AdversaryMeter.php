<?php

namespace App\Helpers;

use App\User;
use Illuminate\Support\Facades\Auth;

/** @deprecated */
class AdversaryMeter
{
    public static function redirectUrl(string $tab = ''): string
    {
        /** @var User $user */
        $user = Auth::user();
        $token = $user->adversaryMeterApiToken(); // TODO : throw an error if not set ?
        $url = app_url();
        return asset('adversary_meter') . "/src/index.html?api_token={$token}&api_url={$url}&tab={$tab}";
    }
}