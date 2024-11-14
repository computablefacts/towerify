<?php

namespace App\Listeners;

use App\Models\Role;
use App\User;
use Illuminate\Support\Facades\Auth;
use Konekt\User\Events\UserInvitationUtilized;

class UserInvitationUtilizedListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof UserInvitationUtilized)) {
            throw new \Exception('Invalid event type!');
        }

        $invitation = $event->getInvitation();
        /** @var User $created */
        $created = User::where('id', $invitation->user_id)->first();

        if ($created) {

            /** @var User $creator */
            $creator = User::where('id', $invitation->created_by)->first();

            if ($creator) {

                Auth::login($creator); // otherwise the tenant will not be properly set

                // Set tenant
                if ($creator->tenant_id) {
                    $created->tenant_id = $creator->tenant_id;
                }

                // Set customer
                if ($creator->customer_id) {
                    $created->customer_id = $creator->customer_id;
                }

                // Add the 'basic end user' role to the user
                $basicEndUser = Role::where('name', Role::BASIC_END_USER)->first();

                if ($basicEndUser) {
                    $created->roles()->syncWithoutDetaching($basicEndUser);
                }

                $limitedAdministrator = Role::where('name', Role::LIMITED_ADMINISTRATOR)->first();

                if ($limitedAdministrator) {
                    $created->roles()->syncWithoutDetaching($limitedAdministrator);
                }

                $created->save();
            }
        }
    }
}
