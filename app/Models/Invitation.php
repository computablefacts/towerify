<?php

namespace App\Models;

use App\Hashing\TwHasher;
use App\Traits\HasTenant;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Konekt\AppShell\Models\Invitation as InvitationBase;
use Konekt\User\Contracts\User as UserContract;
use Konekt\User\Events\UserInvitationUtilized;
use Konekt\User\Events\UserIsBeingCreatedFromInvitation;
use Konekt\User\Models\UserProxy;

class Invitation extends InvitationBase
{
    use HasTenant;

    public function createUser(array $furtherAttributes = [], string $userClass = null, bool $dontEncryptPassword = false): UserContract
    {
        /** @var User $creator */
        $creator = User::where('id', $this->created_by)->first();

        $attributes = array_merge([
            'email' => $this->email,
            'name' => $this->name,
            'type' => $this->type,
            'tenant_id' => $creator?->tenant_id ?? null,
            'customer_id' => $creator?->customer_id ?? null,
        ], $furtherAttributes);

        if (!$dontEncryptPassword && isset($attributes['password'])) {
            $attributes['password'] = TwHasher::hash($attributes['password']);
        }

        $userClass = $userClass ?? UserProxy::modelClass();
        /** @var Model $user */
        $user = new $userClass();
        $user->fill($attributes);

        event(new UserIsBeingCreatedFromInvitation($this, $user));

        $user->push();

        // Add the 'cyberbuddy only' role to some user
        if ($user->tenant_id === 18 && $user->customer_id === 9) {

            $cyberBuddyEndUser = Role::where('name', Role::CYBERBUDDY_ONLY)->first();

            if ($cyberBuddyEndUser) {
                $user->roles()->syncWithoutDetaching($cyberBuddyEndUser);
            }
        } else {

            // Add the 'basic end user' role to the user
            $basicEndUser = Role::where('name', Role::BASIC_END_USER)->first();

            if ($basicEndUser) {
                $user->roles()->syncWithoutDetaching($basicEndUser);
            }

            // Add the 'limited administrator' role to the user
            $limitedAdministrator = Role::where('name', Role::LIMITED_ADMINISTRATOR)->first();

            if ($limitedAdministrator) {
                $user->roles()->syncWithoutDetaching($limitedAdministrator);
            }
        }

        User::init($user);

        $this->user_id = $user->id;
        $this->utilized_at = Carbon::now();
        $this->save();

        event(new UserInvitationUtilized($this));

        return $user;
    }
}