<?php

namespace App\Helpers;

use App\Models\Saml2Tenant;
use Illuminate\Support\Str;

class SamlExternalAuth
{
    public static function authenticate(string $email)
    {
        $samlTenant = Saml2Tenant::firstFromDomain(Str::afterLast($email, '@'));
        if ($samlTenant) {
            return redirect()->intended(saml_url(config('devdojo.auth.settings.redirect_after_auth'), $samlTenant->uuid));
        }
        return null;
    }
}
