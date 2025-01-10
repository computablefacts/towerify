<?php

namespace App\Http\Controllers;

use App\User;

class UserController extends Controller
{
    public function toggleGetsAuditReport(User $user)
    {
        $user->gets_audit_report = !$user->gets_audit_report;
        $user->save();
        return response()->json(['success' => "Your settings have been updated."]);
    }
}
