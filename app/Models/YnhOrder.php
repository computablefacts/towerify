<?php

namespace App\Models;

use App\Enums\ProductTypeEnum;
use App\Helpers\ProductOrProductVariant;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int order_id
 * @property int order_item_id
 * @property ProductTypeEnum product_type
 */
class YnhOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'product_type',
    ];

    protected $casts = [
        'product_type' => ProductTypeEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function forUser(User $user): Collection
    {
        if (!$user) {
            return collect();
        }
        if ($user->tenant_id) {
            if ($user->customer_id) {
                return YnhOrder::with('order', 'orderItem')
                    ->select('ynh_orders.*')
                    ->join('orders', 'orders.id', '=', 'ynh_orders.order_id')
                    ->join('users', 'users.id', '=', 'orders.created_by')
                    ->whereRaw("(users.tenant_id IS NULL OR users.tenant_id = {$user->tenant_id})")
                    ->whereRaw("(users.customer_id IS NULL OR users.customer_id = {$user->customer_id})")
                    ->orderBy('ynh_orders.created_at', 'desc')
                    ->get();
            }
            return YnhOrder::with('order', 'orderItem')
                ->select('ynh_orders.*')
                ->join('orders', 'orders.id', '=', 'ynh_orders.order_id')
                ->join('users', 'users.id', '=', 'orders.created_by')
                ->whereRaw("(users.tenant_id IS NULL OR users.tenant_id = {$user->tenant_id})")
                ->orderBy('ynh_orders.created_at', 'desc')
                ->get();
        }
        return YnhOrder::with('order', 'orderItem')
            ->select('ynh_orders.*')
            ->orderBy('ynh_orders.created_at', 'desc')
            ->get();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id', 'id');
    }

    public function thumbnailUrl(): ?string
    {
        return $this->product()->getThumbnailUrl();
    }

    public function name(): string
    {
        return $this->product()->name();
    }

    public function sku(): string
    {
        return $this->product()->sku();
    }

    public function taxons(): Collection
    {
        return $this->product()->taxons()->get();
    }

    public function orderIdentifier(): string
    {
        return $this->order->number;
    }

    public function orderStatus(): string
    {
        return $this->order->status->label();
    }

    public function orderIsProcessing(): bool
    {
        return $this->orderStatus() === 'Processing';
    }

    public function orderIsCompleted(): bool
    {
        return $this->orderStatus() === 'Completed';
    }

    public function orderIsCancelled(): bool
    {
        return $this->orderStatus() === 'Cancelled';
    }

    public function isFulfilled(): bool
    {
        return $this->orderItem?->fulfillment_status && $this->orderItem->fulfillment_status->isFulfilled();
    }

    public function isServer(): bool
    {
        return $this->product_type === ProductTypeEnum::SERVER;
    }

    public function isApplication(): bool
    {
        return $this->product_type === ProductTypeEnum::APPLICATION;
    }

    public function isServerDeployed(): bool
    {
        if ($this->isServer()) {
            $server = YnhServer::where('ynh_order_id', $this->id)->first();
            return $server && $server->isReady();
        }
        return false;
    }

    public function isServerDeployable(): bool
    {
        if ($this->isServer()) {
            $server = YnhServer::where('ynh_order_id', $this->id)->first();
            return !$server || !$server->isReady();
        }
        return false;
    }

    public function isApplicationDeployed(): bool
    {
        if ($this->isApplication()) {
            return $this->isFulfilled() && YnhApplication::where('ynh_order_id', $this->id)->exists();
        }
        return false;
    }

    public function isApplicationDeployable(): bool
    {
        if ($this->isApplication()) {
            return $this->isFulfilled() && !YnhApplication::where('ynh_order_id', $this->id)->exists();
        }
        return false;
    }

    public function product(): ProductOrProductVariant
    {
        return ProductOrProductVariant::create($this->orderItem->product);
    }
}
