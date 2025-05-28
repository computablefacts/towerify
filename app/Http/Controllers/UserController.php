<?php

namespace App\Http\Controllers;

use App\Http\Procedures\UsersProcedure;
use App\User;
use Illuminate\Http\Request;

/** @deprecated */
class UserController extends Controller
{
    public function toggleGetsAuditReport(User $user)
    {
        return response()->json([
            'success' => (new UsersProcedure())->toggleGetsAuditReport(new Request([
                'user_id' => $user->id,
            ]))['msg'],
        ]);
    }
}
