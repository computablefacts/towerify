<?php

namespace App\Listeners;

use App\Models\Invitation;
use App\Models\Saml2Tenant;
use App\User;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Konekt\User\Models\InvitationProxy;
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
        }

        $user = $this->createOrUpdateUser();

        // Connect the user
        Auth::login($user);

        if ($debug) Log::debug('[SAML2 Authentication] handleSignedIn ends');
    }

    public function handleSignedOut(SignedOut $event): void
    {
        $debug = config('app.debug');
        if ($debug) Log::debug('[SAML2 Authentication] handleSignedOut begins');

        Auth::logout();
        Session::save();

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
        $saml2UserAttributes = $this->saml2User->getAttributes();

        if ($debug) Log::debug('SAML2 Attributes', [
            'saml2UserFriendlyNameAttributes' => $saml2UserFriendlyNameAttributes,
            'saml2UserAttributes' => $saml2UserAttributes,
        ]);

        // First, try with Friendly Names of attributes
        // TODO: put friendly name in tenant JSON config
        $emailFriendlyName = 'email';
        $saml2UserEmail = $saml2UserFriendlyNameAttributes[$emailFriendlyName][0] ?? '';
        if ($saml2UserEmail != '') {
            Log::debug('[SAML2 Authentication] User email found: "' . $saml2UserEmail . '" (from friendly name "' . $emailFriendlyName . '")');
        }

        // Second, try with "full" names of attribute
        if ($saml2UserEmail == '') {
            // TODO: put full name in tenant JSON config
            $emailKey = 'http://schemas.xmlsoap.org/claims/EmailAddress';
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
        // TODO: put friendly name in tenant JSON config
        $nameFriendlyName = 'name';
        $saml2UserName = $saml2UserFriendlyNameAttributes[$nameFriendlyName][0] ?? '';
        if ($saml2UserName != '') {
            Log::debug('[SAML2 Authentication] User name found: "' . $saml2UserName . '" (from friendly name "' . $nameFriendlyName . '")');
        }

        // Second, try with "full" names of attribute
        if ($saml2UserName == '') {
            // TODO: put full name in tenant JSON config
            $nameKey = 'http://schemas.xmlsoap.org/claims/CommonName';
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
        // TODO: put friendly name in tenant JSON config
        $roleFriendlyName = 'Role';
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
        // TODO: put full name in tenant JSON config
        $roleKey = 'http://schemas.microsoft.com/ws/2008/06/identity/claims/role';
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
        $tenantId = $this->saml2Tenant->getTenantId(); // Cywise Tenant ID
        $customerId = $this->saml2Tenant->getCustomerId();

        $user = User::query()
            ->where('email', '=', $this->saml2UserEmail)
            ->where('tenant_id', '=', $tenantId)
            ->where('customer_id', '=', $customerId)
            ->first();

        if (!$user) {
            Log::info('[SAML2 Authentication] User does not exist, we create it');

            $invitation = Invitation::query()
                ->where('email', $this->saml2UserEmail)
                ->first();
            if (!$invitation) {
                $invitation = InvitationProxy::createInvitation($this->saml2UserEmail, $this->saml2UserName);
            }
            $user = $invitation->createUser([
                'password' => Str::random(64),
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
            ]);
        } else {
            Log::info('[SAML2 Authentication] User already exist, we update attributes');

            $user->name = $this->saml2UserName;
            // TODO: change password if option set 'putRandomPassword'
            //$user->password = 'xxx';
            $user->save();
        }

        // TODO: sync roles with those from SAML2 claims

        return $user;
    }
}
