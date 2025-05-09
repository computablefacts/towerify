<?php

namespace App\Http\Controllers;

use App\Helpers\PasswordRequirements;
use Illuminate\Validation\Rules\Password;
use Konekt\AppShell\Http\Controllers\PublicInvitationController;
use Konekt\Gears\Facades\Settings;
use Konekt\User\Contracts\Invitation;
use Konekt\User\Models\InvitationProxy;

class PasswordRequirementsPublicInvitationController extends PublicInvitationController
{
    public function show(string $hash)
    {
        /** @var Invitation $invitation */
        $invitation = InvitationProxy::findByHash($hash);

        if (!$invitation) {
            abort(404);
        }

        return view('appshell::public-invitation.show', $this->processViewData(__METHOD__, [
            'invitation' => $invitation,
            'appname' => Settings::get('appshell.ui.name'),
            'passwordRequirements' => (new PasswordRequirements(Password::defaults()))->getRequirements(),
        ]));
    }
}
