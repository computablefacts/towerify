<?php

namespace App\Modules\AdversaryMeter\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Modules\AdversaryMeter\Events\CreateAsset;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\AssetTag;
use App\Modules\AdversaryMeter\Rules\IsValidAsset;
use App\Modules\AdversaryMeter\Rules\IsValidDomain;
use App\Modules\AdversaryMeter\Rules\IsValidIpAddress;
use App\Modules\AdversaryMeter\Rules\IsValidTag;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateAssetListener extends AbstractListener
{
    public static function execute(User $user, string $asset, bool $monitor, array $tags = []): ?Asset
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
        /** @var Asset $azzet */
        $azzet = Asset::where('asset', $asset)->first();
        if (!$azzet) {
            $azzet = Asset::create([
                'asset' => $asset,
                'type' => $assetType,
                'is_monitored' => $monitor,
                'created_by' => $user->id,
            ]);
            collect($tags)->map(fn(string $tag) => Str::lower($tag))
                ->filter(fn(string $tag) => IsValidTag::test($tag))
                ->each(function (string $tag) use ($azzet) {
                    /** @var ?AssetTag $obj */
                    $obj = $azzet->tags()->where('tag', $tag)->first();
                    if (!$obj) {
                        $obj = $azzet->tags()->create(['tag' => $tag]);
                    }
                });
        }
        return $azzet;
    }

    protected function handle2($event)
    {
        if (!($event instanceof CreateAsset)) {
            throw new \Exception('Invalid event type!');
        }
        Auth::login($event->user); // otherwise the tenant will not be properly set
        self::execute($event->user, $event->asset, $event->monitor, $event->tags);
    }
}
