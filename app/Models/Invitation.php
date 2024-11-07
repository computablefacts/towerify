<?php

namespace App\Models;

use App\Hashing\TwHasher;
use App\Traits\HasTenant;
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
        $attributes = array_merge([
            'email' => $this->email,
            'name' => $this->name,
            'type' => $this->type,
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

        $this->user_id = $user->id;
        $this->utilized_at = Carbon::now();
        $this->save();

        event(new UserInvitationUtilized($this));

        return $user;
    }
}