<?php

namespace App\Traits;

use App\Models\Tenant;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * This trait scopes a query using the `created_by` field of the model it is added to.
 */
trait HasTenant
{
    protected static function booted()
    {
        parent::booted();
        static::addGlobalScope('tenant_scope', function (Builder $builder) {

            $user = Auth::user();
            $tenantId = $user?->tenant_id;

            if ($tenantId) {

                $customerId = $user->customer_id;

                if ($customerId) {
                    $users = User::select('id')->where('tenant_id', $tenantId)->where('customer_id', $customerId);
                } else {
                    $users = User::select('id')->where('tenant_id', $tenantId);
                }

                $builder->whereIn("{$builder->getModel()->getTable()}.created_by", $users)->orWhereNull('created_by');
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function tenant(): ?Tenant
    {
        return $this->createdBy?->tenant();
    }
}