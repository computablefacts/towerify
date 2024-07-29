<?php

namespace App\Traits;

use App\Models\Tenant;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * This trait scopes a query using the `user_id` field of the model it is added to.
 */
trait HasTenant2
{
    protected static function booted()
    {
        parent::booted();
        static::addGlobalScope('tenant_scope_2', function (Builder $builder) {

            $user = Auth::user();
            $tenantId = $user?->tenant_id;

            if ($tenantId) {

                $customerId = $user->customer_id;

                if ($customerId) {
                    $users = User::select('id')
                        ->whereRaw("(tenant_id IS NULL OR tenant_id = {$tenantId})")
                        ->whereRaw("(customer_id IS NULL OR customer_id = {$customerId})");
                } else {
                    $users = User::select('id')
                        ->whereRaw("(tenant_id IS NULL OR tenant_id = {$tenantId})");
                }

                $builder->whereNull('user_id')
                    ->orWhereIn('user_id', $users);
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function tenant(): ?Tenant
    {
        return $this->createdBy?->tenant();
    }
}