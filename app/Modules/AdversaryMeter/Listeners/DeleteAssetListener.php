<?php

namespace App\Modules\AdversaryMeter\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Modules\AdversaryMeter\Events\DeleteAsset;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Rules\IsValidAsset;
use App\Modules\AdversaryMeter\Rules\IsValidDomain;
use App\Modules\AdversaryMeter\Rules\IsValidIpAddress;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeleteAssetListener extends AbstractListener
{
    public static function execute(User $user, string $asset): bool
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

        Asset::where('asset', $asset)
            ->where('type', $assetType)
            ->where('created_by', $user->id)
            ->delete();

        return true;
    }

    public function viaQueue(): string
    {
        return self::CRITICAL;
    }

    protected function handle2($event)
    {
        if (!($event instanceof DeleteAsset)) {
            throw new \Exception('Invalid event type!');
        }
        Auth::login($event->user); // otherwise the tenant will not be properly set
        self::execute($event->user, $event->asset);
    }
}
