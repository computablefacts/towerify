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
