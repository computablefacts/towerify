<?php

namespace App\Traits;

use App\Models\Tenant;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

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
                    $users = User::select('id')
                        ->whereRaw("(tenant_id IS NULL OR tenant_id = {$tenantId})")
                        ->whereRaw("(customer_id IS NULL OR customer_id = {$customerId})");
                } else {
                    $users = User::select('id')
                        ->whereRaw("(tenant_id IS NULL OR tenant_id = {$tenantId})");
                }

                $builder->whereNull('created_by')
                    ->orWhereIn('created_by', $users);
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