<?php

namespace App;

use App\Hashing\TwHasher;
use App\Models\Permission;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

/**
 * DO NOT MOVE OR REMOVE THIS CLASS OTHERWISE EVERYTHING FALLS APART...
 */
class User extends \Konekt\AppShell\Models\User
{
    use HasApiTokens, Billable;

    public function tenant(): ?Tenant
    {
        if ($this->tenant_id) {
            return Tenant::where('id', $this->tenant_id)->first();
        }
        return null;
    }

    public function isBarredFromAccessingTheApp(): bool
    {
        return is_cywise() && // only applies to Cywise deployment
            !$this->isAdmin() && // the admin is always allowed to login
            !$this->isInTrial() && // the trial ended
            $this->customer_id == null && // the customer has not been set yet (automatically set after a successful subscription)
            !$this->subscribed(); // the customer has been set but the subscription ended
    }

    public function isInTrial(): bool
    {
        return $this->customer_id == null && \Carbon\Carbon::now()->startOfDay()->lte($this->endOfTrial());
    }

    public function endOfTrial(): Carbon
    {
        return $this->created_at->startOfDay()->addDays(15);
    }

    public function isAdmin(): bool
    {
        return $this->type->isAdmin();
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

    public function canUseVulnerabilityScanner(): bool
    {
        return $this->canUseAdversaryMeter() || $this->hasPermissionTo(Permission::USE_VULNERABILITY_SCANNER);
    }

    public function canUseHoneypots(): bool
    {
        return $this->canUseAdversaryMeter() || $this->hasPermissionTo(Permission::USE_HONEYPOTS);
    }

    public function canUseAgents(): bool
    {
        return $this->canManageServers() || $this->hasPermissionTo(Permission::USE_AGENTS);
    }

    public function canUseCyberBuddy(): bool
    {
        return $this->hasPermissionTo(Permission::USE_CYBER_BUDDY);
    }

    public function canUseYunoHost(): bool
    {
        return $this->canManageApps() || $this->hasPermissionTo(Permission::USE_YUNOHOST);
    }

    public function canUseMarketplace(): bool
    {
        return $this->canBuyStuff() || $this->hasPermissionTo(Permission::USE_MARKETPLACE);
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
