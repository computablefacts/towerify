<?php

namespace App\Listeners;

use App\Models\Invitation;
use App\Models\Role;
use App\Models\Saml2Tenant;
use App\User;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Konekt\User\Models\InvitationProxy;
use Konekt\User\Models\UserType;
use Slides\Saml2\Events\SignedIn;
use Slides\Saml2\Events\SignedOut;
use Slides\Saml2\Saml2User;

class SamlEventSubscriber
{
    protected Saml2Tenant $saml2Tenant;
    protected Saml2User $saml2User;
    protected string $saml2UserEmail;
    protected string $saml2UserName;
    protected array $saml2UserRoles;

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            SignedIn::class => 'handleSignedIn',
            SignedOut::class => 'handleSignedOut',
        ];
    }

    public function handleSignedIn(SignedIn $event): void
    {
        $debug = config('app.debug');
        if ($debug) Log::debug('[SAML2 Authentication] handleSignedIn begins');

        $this->saml2Tenant = $this->getSaml2Tenant($event);
        $this->saml2User = $this->getSaml2User($event);
        $this->saml2UserEmail = $this->getEmail();
        $this->saml2UserName = $this->getName();
        $this->saml2UserRoles = $this->getRoles();

        // Replace name with email if no name found
        if ($this->saml2UserName == '') {
            $this->saml2UserName = $this->saml2UserEmail;
            Log::info('[SAML2 Authentication] Empty user name replace with email: "' . $this->saml2UserName . '"');
        }

        $user = $this->createOrUpdateUser();

        // Connect the user
        Auth::login($user);

        // Keep NameID to send it back when user logout
        session([
            'saml2NameId' => $this->saml2User->getNameId(),
        ]);

        if ($debug) Log::debug('[SAML2 Authentication] handleSignedIn ends');
    }

    public function handleSignedOut(SignedOut $event): void
    {
        $debug = config('app.debug');
        if ($debug) Log::debug('[SAML2 Authentication] handleSignedOut begins');

        // See LoginController::logout
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();

        if ($debug) Log::debug('[SAML2 Authentication] handleSignedOut ends');
    }

    private function getSaml2Tenant(SignedIn $event): Saml2Tenant
    {
        $debug = config('app.debug');

        $tenant = $event->getAuth()->getTenant();
        if ($tenant instanceof Saml2Tenant) {
            return $tenant;
        }

        Log::error('[SAML2 Authentication] Failed: Saml2Tenant not found.');
        abort(401, 'Authentication failed.' . ($debug ? ' Saml2Tenant not found.' : ''));
    }

    private function getSaml2User(SignedIn $event): Saml2User
    {
        $debug = config('app.debug');

        $messageId = $event->getAuth()->getLastMessageId();
        // your own code preventing reuse of a $messageId to stop replay attacks
        // see https://github.com/aacotroneo/laravel-saml2/issues/164
        // => $messageId MUST be seen only one time
        $cacheKey = 'saml2:' . $messageId;
        if (Cache::has($cacheKey)) {
            Log::error('[SAML2 Authentication] Failed: Message ID already seen.');
            abort(401, 'Authentication failed.' . ($debug ? ' Message ID already seen.' : ''));
        }
        // Store messageId for 15 minutes. It would be better to use NotOnOrAfter from SAML message but we
        // cannot easily access to this timestamp.
        Cache::put($cacheKey, 'seen', 15 * 60);

        // Get the SAML2 User if authentication succeed
        $saml2User = $event->getSaml2User();
        if (!$saml2User) {
            Log::error('[SAML2 Authentication] Failed: Saml2User not found in assertion.');
            abort(401, 'Authentication failed.' . ($debug ? ' Saml2User not found in assertion.' : ''));
        }
        return $saml2User;
    }

    private function getEmail(): string
    {
        $debug = config('app.debug');

        $saml2UserFriendlyNameAttributes = $this->saml2User->getAttributesWithFriendlyName();
        Log::debug('[SAML2 Authentication] User Attributes with Friendly Name:', $saml2UserFriendlyNameAttributes);
        $saml2UserAttributes = $this->saml2User->getAttributes();
        Log::debug('[SAML2 Authentication] User Attributes with Name:', $saml2UserAttributes);

        if ($debug) Log::debug('SAML2 Attributes', [
            'saml2UserFriendlyNameAttributes' => $saml2UserFriendlyNameAttributes,
            'saml2UserAttributes' => $saml2UserAttributes,
        ]);

        // First, try with Friendly Names of attributes
        $emailFriendlyName = $this->saml2Tenant->config('claims.email.friendlyName', 'email');
        $saml2UserEmail = $saml2UserFriendlyNameAttributes[$emailFriendlyName][0] ?? '';
        if ($saml2UserEmail != '') {
            Log::debug('[SAML2 Authentication] User email found: "' . $saml2UserEmail . '" (from friendly name "' . $emailFriendlyName . '")');
        }

        // Second, try with "full" names of attribute
        if ($saml2UserEmail == '') {
            $emailKey = $this->saml2Tenant->config('claims.email.name', 'http://schemas.xmlsoap.org/claims/EmailAddress');
            $saml2UserEmail = $saml2UserAttributes[$emailKey][0] ?? '';
            if ($saml2UserEmail != '') {
                Log::debug('[SAML2 Authentication] User email found: "' . $saml2UserEmail . '" (from name "' . $emailKey . '")');
            }
        }

        // Third, take UserId as email if it is an email (contains @)
        if ($saml2UserEmail == '') {
            $nameId = $this->saml2User->getNameId();
            $saml2UserEmail = Str::contains($nameId, '@') ? $nameId : '';
            if ($saml2UserEmail != '') {
                Log::debug('[SAML2 Authentication] User email found: "' . $saml2UserEmail . '" (from NameId "' . $nameId . '")');
            }
        }

        Log::info('[SAML2 Authentication] User email found: "' . $saml2UserEmail . '"');

        // Abort if no email found
        if ($saml2UserEmail == '') {
            Log::error('[SAML2 Authentication] Failed: User email not found.');
            abort(401, 'Authentication failed.' . ($debug ? ' User email not found.' : ''));
        }

        return $saml2UserEmail;
    }

    private function getName(): string
    {
        $saml2UserFriendlyNameAttributes = $this->saml2User->getAttributesWithFriendlyName();
        $saml2UserAttributes = $this->saml2User->getAttributes();

        // First, try with Friendly Names of attributes
        $nameFriendlyName = $this->saml2Tenant->config('claims.name.friendlyName', 'name');
        $saml2UserName = $saml2UserFriendlyNameAttributes[$nameFriendlyName][0] ?? '';
        if ($saml2UserName != '') {
            Log::debug('[SAML2 Authentication] User name found: "' . $saml2UserName . '" (from friendly name "' . $nameFriendlyName . '")');
        }

        // Second, try with "full" names of attribute
        if ($saml2UserName == '') {
            $nameKey = $this->saml2Tenant->config('claims.name.name', 'http://schemas.xmlsoap.org/claims/CommonName');
            $saml2UserName = $saml2UserAttributes[$nameKey][0] ?? '';
            if ($saml2UserName != '') {
                Log::debug('[SAML2 Authentication] User name found: "' . $saml2UserName . '" (from name "' . $nameKey . '")');
            }
        }

        Log::info('[SAML2 Authentication] User name found: "' . $saml2UserName . '"');

        return $saml2UserName;
    }

    private function getRoles(): array
    {
        $roles = [];
        $saml2UserFriendlyNameAttributes = $this->saml2User->getAttributesWithFriendlyName();
        $saml2UserAttributes = $this->saml2User->getAttributes();

        // First, try with Friendly Names of attributes
        $roleFriendlyName = $this->saml2Tenant->config('claims.role.friendlyName', 'role');
        if (isset($saml2UserFriendlyNameAttributes[$roleFriendlyName])) {
            Log::info('[SAML2 Authentication] Role attribute found from friendly name "' . $roleFriendlyName . '"');
            $roles = $saml2UserFriendlyNameAttributes[$roleFriendlyName];
            foreach ($roles as $role) {
                Log::info('[SAML2 Authentication] Role value=' . $role);
            }
        } else {
            Log::info('[SAML2 Authentication] Role attribute NOT found from friendly name "' . $roleFriendlyName . '"');
        }

        // Second, try with "full" names of attribute
        $roleKey = $this->saml2Tenant->config('claims.role.name', 'http://schemas.microsoft.com/ws/2008/06/identity/claims/role');
        if (isset($saml2UserAttributes[$roleKey])) {
            Log::info('[SAML2 Authentication] Role attribute found from name "' . $roleKey . '"');
            $roles = $saml2UserAttributes[$roleKey];
            foreach ($roles as $role) {
                Log::info('[SAML2 Authentication] Role value=' . $role);
            }
        } else {
            Log::info('[SAML2 Authentication] Role attribute NOT found from name "' . $roleKey . '"');
        }

        return $roles;
    }

    private function createOrUpdateUser(): User
    {
        $debug = config('app.debug');

        $tenantId = $this->saml2Tenant->getTenantId(); // Cywise Tenant ID
        $customerId = $this->saml2Tenant->getCustomerId();

        $user = User::query()
            ->where('email', '=', $this->saml2UserEmail)
            ->first();

        if (!$user) {
            Log::info('[SAML2 Authentication] User does not exist, we create it');

            $invitation = Invitation::query()
                ->where('email', $this->saml2UserEmail)
                ->first();
            if (!$invitation) {
                $invitation = InvitationProxy::createInvitation(
                    $this->saml2UserEmail,
                    $this->saml2UserName,
                    UserType::CLIENT(),
                    [
                        'tenant_id' => $tenantId,
                        'customer_id' => $customerId,
                        'terms_accepted' => true,
                    ]
                );
            }
            $user = $invitation->createUser([
                'password' => Str::random(64),
            ]);
        } elseif ($user && $tenantId == $user->tenant_id && $customerId == $user->customer_id) {
            Log::info('[SAML2 Authentication] User already exist, we update attributes');

            $user->name = $this->saml2UserName;
            if ($this->saml2Tenant->config('updateUser.putRandomPassword', false)) {
                Log::info('[SAML2 Authentication] Update user password with a random string to be very secure');
                $user->password = Str::random(64);
            }
            $user->save();
        } else {
            Log::error('[SAML2 Authentication] User already exist but with different IDs', [
                'saml_tenant_id' => $tenantId,
                'saml_customer_id' => $customerId,
                'user_tenant_id' => $user->tenant_id,
                'user_customer_id' => $user->customer_id,
            ]);
            Log::error('[SAML2 Authentication] Failed: User email not found.');
            abort(401, 'Authentication failed.' . ($debug ? ' User already exist but with different IDs.' : ''));
        }

        $this->syncUserRoles($user);

        return $user;
    }

    /**
     *
     * Default roles and roles to synchronize between IdP and Cywise are defined
     * in saml2_tenants.metadata JSON settings. For example:
     *  "roles": {
     *      "default": ["role1", "role2"],
     *      "idp_roles": ["user", "admin"],
     *      "user_to_cywise": ["cywise_user"],
     *      "admin_to_cywise": ["cywise_admin", "cywise_admin2"]
     *   }
     *
     * @param User $user
     * @return void
     */
    private function syncUserRoles(User $user): void
    {
        // Get default roles from settings
        $newRoles = Arr::wrap($this->saml2Tenant->config('roles.default', []));
        Log::debug('[SAML2 Authentication] Default roles =', $newRoles);
        Log::debug('[SAML2 Authentication] SAML roles =', $this->saml2UserRoles);

        // Get link between IdP roles and cywise roles
        // Add Cywise roles based on IdP roles the user has
        $idpRoles = Arr::wrap($this->saml2Tenant->config('roles.idp_roles', []));
        Log::debug('[SAML2 Authentication] IdP roles settings =', $idpRoles);
        foreach ($idpRoles as $idpRole) {
            if (in_array($idpRole, $this->saml2UserRoles)) {
                $cywiseRoles = Arr::wrap($this->saml2Tenant->config('roles.' . $idpRole . '_to_cywise', []));
                Log::debug('[SAML2 Authentication] Cywise roles for ' . $idpRole . ' =', $cywiseRoles);
                $newRoles = array_merge($newRoles, $cywiseRoles);
            }
        }
        Log::debug('[SAML2 Authentication] New roles =', $newRoles);

        $userRoles = collect($newRoles)->map(function ($role) {
            try {
                $dbRole = Role::query()->where('name', '=', constant($role))->first();
                return $dbRole ? $dbRole->id : 'unknown';
            } catch (\Error) {
                return 'unknown';
            }
        })->filter(function ($role) {
            return $role <> 'unknown';
        })->toArray();
        Log::debug('[SAML2 Authentication] User roles IDs =', $userRoles);

        $changes = $user->roles()->sync($userRoles);

        Log::debug('[SAML2 Authentication] User roles IDs updated =', $changes);
    }
}
