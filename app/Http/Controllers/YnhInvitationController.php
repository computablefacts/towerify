<?php

namespace App\Http\Controllers;

use App\Events\SendInvitation;
use App\Http\Requests\CreateInvitationRequest;
use App\Http\Requests\SendInvitationRequest;
use App\Models\Invitation;
use App\User;
use Konekt\User\Models\InvitationProxy;

class YnhInvitationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(CreateInvitationRequest $request)
    {
        if (!$request->exists('users')) {

            $fullname = $request->input('fullname');
            $email = $request->input('email');
            $invitation = InvitationProxy::createInvitation($email, $fullname);

            return response()->json(['success' => "The invitation has been created!"]);
        }

        /** @var array $users */
        $users = $request->input('users');

        foreach ($users as $user) {

            /** @var string $name */
            $name = $user['name'];
            /** @var string $email */
            $email = $user['email'];

            if (!InvitationProxy::where('email', $email)->exists()) {
                if (!User::where('email', $email)->exists()) {
                    $invitation = InvitationProxy::createInvitation($email, $name);
                }
            }
        }
        return response()->json(['success' => "The invitations have been created!"]);
    }

    public function send(SendInvitationRequest $request)
    {
        $invitation = Invitation::query()->where('id', $request->integer('id'))->firstOrFail();
        SendInvitation::dispatch($invitation);
        return response()->json(['success' => "The invitation has been sent!"]);
    }
}