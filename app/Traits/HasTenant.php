<?php

namespace App\Traits;

use App\Models\Tenant;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * This trait scopes a query using the `created_by` field of the model it is added to.
 *
 * @property ?int $created_by
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

                $builder->whereIn("{$builder->getModel()->getTable()}.created_by", $users)
                    ->orWhereNull("{$builder->getModel()->getTable()}.created_by");
            }
        });
    }

    public function createdBy(): User
    {
        return User::where('id', $this->created_by)->firstOrFail();
    }

    public function tenant(): ?Tenant
    {
        return $this->createdBy()->tenant();
    }
}