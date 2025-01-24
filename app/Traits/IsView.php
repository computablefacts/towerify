<?php

namespace App\Traits;

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\YnhOsquery;
use App\Models\YnhServer;
use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait IsView
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(YnhOsquery::class, 'event_id', 'id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class, 'server_id', 'id');
    }

    public function isAdded(): bool
    {
        return $this->action === 'added';
    }

    public function isRemoved(): bool
    {
        return $this->action === 'removed';
    }
}