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
    public static function execute(string $asset): bool
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

        Asset::where('asset', $asset)->where('type', $assetType)->delete();
        return true;
    }

    protected function handle2($event)
    {
        if (!($event instanceof DeleteAsset)) {
            throw new \Exception('Invalid event type!');
        }
        self::execute($event->asset);
    }
}
