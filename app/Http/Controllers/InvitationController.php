<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendInvitationRequest;
use App\Models\Invitation;
use Illuminate\Support\Facades\Auth;
use Konekt\User\Models\UserType;

class InvitationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        return view('settings.invitation.form', [
            'invitation' => [
                'username' => null,
                'email' => null,
            ]
        ]);
    }

    public function send(SendInvitationRequest $request)
    {
        try {
            $invitation = Invitation::createInvitation(
                $request->string('email'),
                $request->string('username'),
                UserType::CLIENT(),
                [
                    'client_id' => Auth::user()->tenant()->id,
                    'customer_id' => Auth::user()->customer_id
                ],
                7
            );
            return response()->json([
                'success' => true,
                'message' => __('Invitation successfully sent.'),
                'invitation' => $invitation,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('Error: :msg', ['msg' => $e->getMessage()])]);
        }
    }
}
