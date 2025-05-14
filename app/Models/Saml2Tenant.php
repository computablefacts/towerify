<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Slides\Saml2\Models\Tenant;

class Saml2Tenant extends Tenant
{
    public static function firstFromDomain(string $domain)
    {
        return self::all()
            ->filter(function (Saml2Tenant $saml2Tenant) use ($domain) {
                $allowedDomains = [
                    $saml2Tenant->domain,
                    $saml2Tenant->alt_domain1,
                ];

                foreach ($allowedDomains as $allowedDomain) {
                    // If SAML Tenant domain starts with ~, it is a regex
                    if (Str::startsWith($allowedDomain, '~')) {
                        if (1 === preg_match($allowedDomain, $domain)) {
                            Log::debug("[SAML2 Authentication] $domain matches on domain regex $allowedDomain");
                            return true;
                        }
                    } else {
                        if ($domain === $allowedDomain) {
                            Log::debug("[SAML2 Authentication] $domain is equal to domain $allowedDomain");
                            return true;
                        }
                    }
                }

                return false;
            })->first();
    }

    public function getTenantId()
    {
        return $this->tenant_id;
    }

    public function getCustomerId()
    {
        return $this->customer_id;
    }

    public function config(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $current = $this->getConfigs();

        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                return $default;
            }
            $current = $current[$k];
        }

        return $current;
    }

    private function getConfigs(): array
    {
        return $this->metadata;
    }
}
