<?php

namespace App\Modules\AdversaryMeter\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Modules\AdversaryMeter\Events\CreateAsset;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Rules\IsValidAsset;
use App\Modules\AdversaryMeter\Rules\IsValidDomain;
use App\Modules\AdversaryMeter\Rules\IsValidIpAddress;
use Illuminate\Support\Facades\Log;

class CreateAssetListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof CreateAsset)) {
            throw new \Exception('Invalid event type!');
        }

        $asset = $event->asset;
        $userId = $event->userId;
        $customerId = $event->customerId;
        $tenantId = $event->tenantId;

        if (!IsValidAsset::test($asset)) {
            Log::error("{$asset} is not a valid asset.");
            return;
        }
        if (IsValidDomain::test($asset)) {
            $assetType = AssetTypesEnum::DNS;
        } elseif (IsValidIpAddress::test($asset)) {
            $assetType = AssetTypesEnum::IP;
        } else {
            $assetType = AssetTypesEnum::RANGE;
        }

        Asset::updateOrCreate(
            [
                'asset' => $asset,
                'user_id' => $userId,
                'customer_id' => $customerId,
                'tenant_id' => $tenantId,
            ],
            [
                'asset' => $asset,
                'asset_type' => $assetType,
                'user_id' => $userId,
                'customer_id' => $customerId,
                'tenant_id' => $tenantId,
            ]
        );
    }
}
