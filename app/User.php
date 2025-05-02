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
 *
 * @property int tenant_id
 * @property Carbon trial_ends_at
 * @property string am_api_token
 * @property string se_api_token
 * @property string stripe_id
 * @property string performa_domain
 * @property string performa_secret
 * @property boolean terms_accepted
 * @property boolean gets_audit_report
 */
class User extends \Konekt\AppShell\Models\User
{
    use HasApiTokens, Billable;

    protected $fillable = [
        'name', 'email', 'password', 'type', 'is_active', 'customer_id', 'tenant_id'
    ];

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

    public function endsTrialSoon(): bool
    {
        return $this->isInTrial() && \Carbon\Carbon::now()->startOfDay()->gte($this->endOfTrial()->subDays(7));
    }

    public function endsTrialVerySoon(): bool
    {
        return $this->isInTrial() && \Carbon\Carbon::now()->startOfDay()->gte($this->endOfTrial()->subDays(3));
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
            /** @var User $userTmp */
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
            /** @var User $userTmp */
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

    public function canViewHome(): bool
    {
        return $this->hasPermissionTo(\App\Models\Permission::VIEW_OVERVIEW)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_METRICS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_EVENTS);
    }

    public function canViewVulnerabilityScanner(): bool
    {
        return $this->hasPermissionTo(\App\Models\Permission::VIEW_ASSETS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_VULNERABILITIES)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_SERVICE_PROVIDER_DELEGATION);
    }

    public function canViewAgents(): bool
    {
        return $this->hasPermissionTo(\App\Models\Permission::VIEW_AGENTS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_SECURITY_RULES);
    }

    public function canViewHoneypots(): bool
    {
        return $this->hasPermissionTo(\App\Models\Permission::VIEW_HONEYPOTS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_ATTACKERS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_IP_BLACKLIST);
    }

    public function canViewIssp(): bool
    {
        return $this->hasPermissionTo(\App\Models\Permission::VIEW_HARDENING)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_FRAMEWORKS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_AI_WRITER)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_CYBERBUDDY)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_CONVERSATIONS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_COLLECTIONS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_DOCUMENTS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_TABLES)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_CHUNKS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_PROMPTS);
    }

    public function canViewYunoHost(): bool
    {
        return $this->hasPermissionTo(\App\Models\Permission::VIEW_DESKTOP)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_SERVERS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_APPLICATIONS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_DOMAINS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_BACKUPS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_INTERDEPENDENCIES)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_TRACES);
    }

    public function canViewMarketplace(): bool
    {
        return $this->isAdmin()
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_PRODUCTS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_CART)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_ORDERS);
    }

    public function canViewSettings(): bool
    {
        return $this->hasPermissionTo(\App\Models\Permission::VIEW_USERS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_INVITATIONS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_PLANS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_MY_SUBSCRIPTION)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_DOCUMENTATION)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_TERMS)
            || $this->hasPermissionTo(\App\Models\Permission::VIEW_RESET_PASSWORD);
    }

    /** @deprecated */
    public function canListServers(): bool
    {
        return $this->hasPermissionTo(Permission::LIST_SERVERS) || $this->canManageServers();
    }

    /** @deprecated */
    public function canManageServers(): bool
    {
        return $this->hasPermissionTo(Permission::MANAGE_SERVERS);
    }

    /** @deprecated */
    public function canListApps(): bool
    {
        return $this->hasPermissionTo(Permission::LIST_APPS) || $this->canManageApps();
    }

    /** @deprecated */
    public function canManageApps(): bool
    {
        return $this->hasPermissionTo(Permission::MANAGE_APPS);
    }

    /** @deprecated */
    public function canListUsers(): bool
    {
        return $this->hasPermissionTo(Permission::LIST_USERS) || $this->canManageUsers();
    }

    /** @deprecated */
    public function canManageUsers(): bool
    {
        return $this->hasPermissionTo(Permission::MANAGE_USERS);
    }

    /** @deprecated */
    public function canListOrders(): bool
    {
        return $this->canListServers() && $this->canListApps();
    }

    /** @deprecated */
    public function canBuyStuff(): bool
    {
        return $this->hasPermissionTo(Permission::BUY_STUFF);
    }

    /** @deprecated */
    public function canUseAdversaryMeter(): bool
    {
        return $this->hasPermissionTo(Permission::USE_ADVERSARY_METER);
    }

    /** @deprecated */
    public function canUseAgents(): bool
    {
        return $this->canManageServers() || $this->hasPermissionTo(Permission::USE_AGENTS);
    }

    /** @deprecated */
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
