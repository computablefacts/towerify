<?php

namespace App\Modules\AdversaryMeter\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Modules\AdversaryMeter\Events\DeleteAsset;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Rules\IsValidAsset;
use App\Modules\AdversaryMeter\Rules\IsValidDomain;
use App\Modules\AdversaryMeter\Rules\IsValidIpAddress;
use Illuminate\Support\Facades\Log;

class DeleteAssetListener extends AbstractListener
{
    public static function execute(string $asset, ?int $userId = null, ?int $customerId = null, ?int $tenantId = null): bool
    {
        if (!IsValidAsset::test($asset)) {
            Log::error("Invalid asset : {$asset}");
            return false;
        }
        if (IsValidDomain::test($asset)) {
            $assetType = AssetTypesEnum::DNS;
        } elseif (IsValidIpAddress::test($asset)) {
            $assetType = AssetTypesEnum::IP;
        } else {
            $assetType = AssetTypesEnum::RANGE;
        }

        $query = Asset::where('asset', $asset)->where('asset_type', $assetType);

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($customerId) {
            $query->where('customer_id', $customerId);
        }
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $query->delete();
        return true;
    }

    protected function handle2($event)
    {
        if (!($event instanceof DeleteAsset)) {
            throw new \Exception('Invalid event type!');
        }
        self::execute($event->asset, $event->userId, $event->customerId, $event->tenantId);
    }
}
