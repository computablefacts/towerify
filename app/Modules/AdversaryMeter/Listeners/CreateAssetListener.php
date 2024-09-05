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
    public static function execute(string $asset): ?Asset
    {
        if (!IsValidAsset::test($asset)) {
            Log::error("Invalid asset : {$asset}");
            return null;
        }
        if (IsValidDomain::test($asset)) {
            $assetType = AssetTypesEnum::DNS;
        } elseif (IsValidIpAddress::test($asset)) {
            $assetType = AssetTypesEnum::IP;
        } else {
            $assetType = AssetTypesEnum::RANGE;
        }
        return Asset::updateOrCreate(
            [
                'asset' => $asset,
            ],
            [
                'asset' => $asset,
                'type' => $assetType,
            ]
        );
    }

    protected function handle2($event)
    {
        if (!($event instanceof CreateAsset)) {
            throw new \Exception('Invalid event type!');
        }
        self::execute($event->asset);
    }
}
