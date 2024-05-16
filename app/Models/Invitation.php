<?php

namespace App\Models;

use App\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Konekt\AppShell\Models\Invitation as InvitationBase;

class Invitation extends InvitationBase
{
    use HasTenant;

    public function scopePending(Builder $query)
    {
        return $query
            ->whereNull(DB::raw('invitations.user_id'))
            ->where('expires_at', '>', Carbon::now()->toDateTimeString());
    }
}