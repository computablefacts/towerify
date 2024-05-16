<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInvitationRequest;
use Konekt\User\Models\InvitationProxy;

class YnhInvitationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(CreateInvitationRequest $request)
    {
        $fullname = $request->input('fullname');
        $email = $request->input('email');
        $invitation = InvitationProxy::createInvitation($email, $fullname);

        return response()->json(['success' => "The invitation has been created!"]);
    }
}