<?php

namespace App\Http\Procedures;

use App\Models\User;
use Illuminate\Http\Request;
use Sajya\Server\Attributes\RpcMethod;
use Sajya\Server\Procedure;

class UsersProcedure extends Procedure
{
    public static string $name = 'users';

    #[RpcMethod(
        description: "Toggle the envoy of the daily email report to a given user.",
        params: [
            "user_id" => "The user id.",
        ],
        result: [
            "msg" => "A success message.",
        ]
    )]
    public function toggleGetsAuditReport(Request $request): array
    {
        $params = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        /** @var User $loggedInUser */
        $loggedInUser = $request->user();

        /** @var User $user */
        $user = User::query()
            ->where('id', '=', $params['id'])
            ->when($user->tenant_id, fn($query) => $query->where('tenant_id', '=', $user->tenant_id))
            ->when($user->customer_id, fn($query) => $query->where('customer_id', '=', $user->customer_id))
            ->first();

        if (!$user) {
            throw new \Exception("This user does not belong to your tenant.");
        }

        $user->gets_audit_report = !$user->gets_audit_report;
        $user->save();

        return [
            "msg" => "The user {$user->name} settings have been updated."
        ];
    }
}
