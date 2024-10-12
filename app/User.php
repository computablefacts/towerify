<?php

namespace App;

use App\Hashing\TwHasher;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * DO NOT MOVE OR REMOVE THIS CLASS OTHERWISE EVERYTHING FALLS APART...
 */
class User extends \Konekt\AppShell\Models\User
{
    use HasApiTokens;

    public function tenant(): ?Tenant
    {
        if ($this->tenant_id) {
            return Tenant::where('id', $this->tenant_id)->first();
        }
        return null;
    }

    public function isAdmin(): bool
    {
        return $this->type->isAdmin();
    }

    /** @deprecated */
    public function isCywiseUser(): bool
    {
        return $this->hasRole(Role::CYWISE_USER);
    }

    public function adversaryMeterApiToken(): ?string
    {
        if (!$this->canUseAdversaryMeter()) {
            return null;
        }
        if ($this->am_api_token) {
            return $this->am_api_token;
        }

        $tenantId = $this->tenant_id;
        $customerId = $this->customer_id;

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

        // This token will enable the user to configure AdversaryMeter through the user interface
        $token = $this->createToken('adversarymeter', ['']);
        $plainTextToken = $token->plainTextToken;

        $this->am_api_token = $plainTextToken;
        $this->save();

        return $plainTextToken;
    }

    public function sentinelApiToken(): ?string
    {
        if (!$this->canManageServers()) {
            return null;
        }
        if ($this->se_api_token) {
            return $this->se_api_token;
        }

        // This token will enable the user to configure servers using curl
        $token = $this->createToken('sentinel', []);
        $plainTextToken = $token->plainTextToken;

        $this->se_api_token = $plainTextToken;
        $this->save();

        return $plainTextToken;
    }

    public function canListServers(): bool
    {
        return $this->hasPermissionTo(Permission::LIST_SERVERS) || $this->canManageServers();
    }

    public function canManageServers(): bool
    {
        return $this->hasPermissionTo(Permission::MANAGE_SERVERS);
    }

    public function canListApps(): bool
    {
        return $this->hasPermissionTo(Permission::LIST_APPS) || $this->canManageApps();
    }

    public function canManageApps(): bool
    {
        return $this->hasPermissionTo(Permission::MANAGE_APPS);
    }

    public function canListUsers(): bool
    {
        return $this->hasPermissionTo(Permission::LIST_USERS) || $this->canManageUsers();
    }

    public function canManageUsers(): bool
    {
        return $this->hasPermissionTo(Permission::MANAGE_USERS);
    }

    public function canListOrders(): bool
    {
        return $this->canListServers() && $this->canListApps();
    }

    public function canBuyStuff(): bool
    {
        return $this->hasPermissionTo(Permission::BUY_STUFF);
    }

    public function canUseAdversaryMeter(): bool
    {
        return $this->hasPermissionTo(Permission::USE_ADVERSARY_METER);
    }

    public function canUseCyberBuddy(): bool
    {
        return $this->hasPermissionTo(Permission::USE_CYBER_BUDDY);
    }

    public function ynhUsername(): string
    {
        return Str::lower(Str::before(Str::before($this->email, '@'), '+'));
    }

    public function ynhPassword(): string
    {
        return TwHasher::unhash($this->password);
    }

    public function client(): string
    {
        if ($this->customer_id) {
            return "cid{$this->customer_id}";
        }
        if ($this->tenant_id) {
            return "tid{$this->tenant_id}";
        }
        return "tid0-cid0";
    }
}
