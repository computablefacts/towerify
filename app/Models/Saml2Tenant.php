<?php

namespace App\Models;

use Slides\Saml2\Models\Tenant;

class Saml2Tenant extends Tenant
{
    public static function firstFromDomain(string $domain)
    {
        return self::query()
            ->where('domain', '=', $domain)
            ->orWhere('alt_domain1', '=', $domain)
            ->first();
    }

    public function getTenantId()
    {
        return $this->tenant_id;
    }

    public function getCustomerId()
    {
        return $this->customer_id;
    }
}
