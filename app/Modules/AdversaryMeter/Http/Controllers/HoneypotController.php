<?php

namespace App\Modules\AdversaryMeter\Http\Controllers;

use App\Modules\AdversaryMeter\Enums\HoneypotCloudProvidersEnum;
use App\Modules\AdversaryMeter\Enums\HoneypotCloudSensorsEnum;
use App\Modules\AdversaryMeter\Enums\HoneypotStatusesEnum;
use App\Modules\AdversaryMeter\Mail\HoneypotRequested;
use App\Modules\AdversaryMeter\Models\Alert;
use App\Modules\AdversaryMeter\Models\AssetTag;
use App\Modules\AdversaryMeter\Models\AssetTagHash;
use App\Modules\AdversaryMeter\Models\Attacker;
use App\Modules\AdversaryMeter\Models\HiddenAlert;
use App\Modules\AdversaryMeter\Models\Honeypot;
use App\Modules\AdversaryMeter\Models\HoneypotEvent;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class HoneypotController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function attackerIndex(Request $request): array
    {
        $totalNumberOfEvents = HoneypotEvent::count();
        return Attacker::select('attackers.*')
            ->orderBy('attackers.name')
            ->orderBy('attackers.last_contact')
            ->get()
            ->map(function (Attacker $attacker) use ($totalNumberOfEvents) {
                return [
                    'id' => $attacker->id,
                    'name' => $attacker->name,
                    'first_contact' => $attacker->first_contact->format('Y-m-d H:i') . ' UTC',
                    'last_contact' => $attacker->last_contact->format('Y-m-d H:i') . ' UTC',
                    'aggressiveness' => $attacker->aggressiveness($totalNumberOfEvents),
                    'ips' => $attacker->ips(),
                ];
            })
            ->toArray();
    }

    public function recentEvents(Request $request): array
    {
        $manual = $request->boolean('manual', true);
        $auto = $request->boolean('auto', true);

        if (!$manual && !$auto) {
            return [];
        }

        /** @var array $ips */
        $ips = config('towerify.adversarymeter.ip_addresses');
        $events = HoneypotEvent::select(
            'honeypots_events.*',
            DB::raw("CASE WHEN attackers.name IS NULL THEN '-' ELSE attackers.name END AS internal_name"),
            DB::raw("CASE WHEN attackers.id IS NULL THEN '-' ELSE attackers.id END AS attacker_id"),
        )
            ->whereNotIn('ip', $ips)
            ->leftJoin('attackers', 'attackers.id', '=', 'honeypots_events.attacker_id');

        if ($auto && !$manual) {
            $events->where('human', true);
        } else if (!$auto && $manual) {
            $events->where('targeted', true);
        }
        return $events->orderBy('timestamp', 'desc')
            ->limit(1000)
            ->get()
            ->toArray();
    }

    public function blacklistIps(?int $attackerId = null)
    {
        /** @var array $ips */
        $ips = config('towerify.adversarymeter.ip_addresses');
        $events = HoneypotEvent::select(
            'honeypots_events.*',
            DB::raw("attackers.first_contact AS first_contact"),
            DB::raw("attackers.last_contact AS last_contact"),
            DB::raw("CASE WHEN honeypots_events.hosting_service_description IS NULL THEN '-' ELSE honeypots_events.hosting_service_description END AS isp_name"),
            DB::raw("CASE WHEN honeypots_events.hosting_service_country_code IS NULL THEN '-' ELSE honeypots_events.hosting_service_country_code END AS country_code"),
        )
            ->whereNotIn('ip', $ips);

        if (!$attackerId) {
            $events->leftJoin('attackers', 'attackers.id', '=', 'honeypots_events.attacker_id');
        } else {
            $events->join('attackers', 'attackers.id', '=', 'honeypots_events.attacker_id');
            $events->where('honeypots_events.attacker_id', $attackerId);
        }
        return $events->get()->toArray();
    }

    public function getVulnerabilitiesWithAssetInfo(?int $attackerId = null): array
    {
        return Alert::select('alerts.*', 'assets.id AS asset_id')
            ->join('ports', 'ports.id', '=', 'alerts.port_id')
            ->join('scans', 'scans.id', '=', 'ports.scan_id')
            ->join('assets', 'assets.cur_scan_id', '=', 'scans.ports_scan_id')
            ->get()
            ->map(function (Alert $alert) use ($attackerId) {
                return [
                    'alert' => $alert,
                    'asset' => $alert->asset(),
                    'port' => $alert->port(),
                    'events' => $alert->events($attackerId)->get()->toArray(),
                ];
            })
            ->toArray();
    }

    public function getVulnerabilitiesWithAssetInfo2(string $assetBase64): array
    {
        return Alert::select('alerts.*', 'assets.id AS asset_id')
            ->join('ports', 'ports.id', '=', 'alerts.port_id')
            ->join('scans', 'scans.id', '=', 'ports.scan_id')
            ->join('assets', 'assets.cur_scan_id', '=', 'ports.ports_scan_id')
            ->where('assets.asset', base64_decode($assetBase64))
            ->get()
            ->map(function (Alert $alert) {
                return [
                    'alert' => $alert,
                    'asset' => $alert->asset(),
                    'events' => $alert->events()->get()->toArray(),
                ];
            })
            ->toArray();
    }

    public function attackerActivity(Attacker $attacker): array
    {
        $events = $attacker->events()->orderBy('timestamp', 'desc')->get();
        return [
            'firstEvent' => $events->pluck('timestamp')->last(),
            'top3EventTypes' => $events->groupBy('event')
                ->sort(fn($e1, $e2) => $e2->count() - $e1->count())
                ->take(3)
                ->map(fn($events, $type) => [
                    'type' => $type,
                    'count' => $events->count(),
                ])
                ->toArray(),
            'events' => $events->take(1000)->toArray(),
        ];
    }

    public function attackerProfile(Attacker $attacker): array
    {
        return [
            'id' => $attacker->id,
            'name' => $attacker->name,
            'first_contact' => $attacker->first_contact->format('Y-m-d H:i') . ' UTC',
            'last_contact' => $attacker->last_contact->format('Y-m-d H:i') . ' UTC',
            'count' => $attacker->events()->count(),
            'tot' => HoneypotEvent::count(),
            'aggressiveness' => $attacker->aggressiveness(),
        ];
    }

    public function attackerStats(Attacker $attacker): array
    {
        return [
            'attacks' => $attacker->events()->count(),
            'human' => $attacker->humans()->count(),
            'targeted' => $attacker->targeted()->count(),
            'cve' => $attacker->cves()->count(),
        ];
    }

    public function getMostRecentEvent(?int $attackerId = null): array
    {
        if ($attackerId) {
            return Attacker::find($attackerId)
                ->events()
                ->orderBy('timestamp', 'desc')
                ->limit(3)
                ->get()
                ->toArray();
        }
        return HoneypotEvent::query()
            ->orderBy('timestamp', 'desc')
            ->limit(3)
            ->get()
            ->toArray();
    }

    public function attackerTools(Attacker $attacker): array
    {
        return $attacker->tools()->toArray();
    }

    public function attackerCompetency(Attacker $attacker): array
    {
        $tools = $attacker->tools()->count();
        $cves = $attacker->cves()->count();
        $humans = $attacker->humans()->count();
        $ips = $attacker->ips()->count();
        $targetedWordlists = $attacker->events()->where('event', 'curated_wordlist')->count();
        $targetedPasswords = $attacker->events()->where('event', 'manual_actions_password_targeted')->count();
        return [
            'toolbox' => min($tools * 1, 10),
            'cve_collection' => min($cves / 1000 * 100, 10),
            'manual_testing' => min($humans / 50 * 100, 10),
            'stealth_tech' => min($ips / 5 * 10, 10),
            'curated_wordlist' => $targetedPasswords ? 10 : min($targetedWordlists / 50 * 100, 10),
            'persistence' => min($attacker->first_contact->diffInDays($attacker->last_contact) / 10 * 10, 10),
        ];
    }

    public function lastHoneypots(): array
    {
        return Honeypot::query()
            ->orderBy('dns')
            ->limit(3)
            ->get()
            ->toArray();
    }

    public function getHoneypotEventStats(string $dns): array
    {
        $honeypot = Honeypot::where('dns', $dns)->first();
        return HoneypotEvent::select(
            DB::raw("DATE_FORMAT(timestamp, '%Y-%m-%d') AS date"),
            DB::raw("SUM(CASE WHEN human = 1 OR targeted = 1 THEN 1 ELSE 0 END) AS human_or_targeted"),
            DB::raw("SUM(CASE WHEN human = 0 AND targeted = 0 THEN 1 ELSE 0 END) AS not_human_or_targeted")
        )
            ->where('honeypot_id', $honeypot->id)
            ->groupBy(DB::raw("DATE_FORMAT(timestamp, '%Y-%m-%d')"))
            ->get()
            ->toArray();
    }

    public function getAlertStats(): array
    {
        return Alert::select(
            DB::raw("level"),
            DB::raw("COUNT(*) AS count")
        )
            ->groupBy('level')
            ->get()
            ->toArray();
    }

    public function honeypotsStatus(): array
    {
        $statuses = [
            HoneypotStatusesEnum::SETUP_COMPLETE,
            HoneypotStatusesEnum::HONEYPOT_SETUP,
            HoneypotStatusesEnum::DNS_SETUP
        ];

        $honeypots = Honeypot::all();

        /** @var HoneypotStatusesEnum $leastAdvancedStatus */
        $leastAdvancedStatus = $honeypots->reduce(function (HoneypotStatusesEnum $carry, Honeypot $honeypot) use ($statuses) {
            $currentStatusIndex = array_search($honeypot->status(), $statuses);
            $carryIndex = array_search($carry, $statuses);
            return $currentStatusIndex < $carryIndex ? $honeypot->status() : $carry;
        }, HoneypotStatusesEnum::DNS_SETUP);

        return [
            'current_user' => Auth::user()->name,
            'honeypots' => $honeypots,
            'integration_status' => $honeypots->count() ? $leastAdvancedStatus : 'inactive'
        ];
    }

    public function postHoneypots(Request $request): array
    {
        $honeypots = $request->validate([
            'honeypots' => [
                'required',
                'array',
                'min:1',
                'max:3',
            ],
            'honeypots.*.dns' => 'regex:/^(?!-)[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}$/',
            'honeypots.*.cloud_provider' => 'string|required',
            'honeypots.*.sensor' => 'string|required',
        ])['honeypots'];

        $count = Honeypot::count();

        if ($count + count($honeypots) > 3) {
            abort(500, 'You already have the maximum number of honeypots allowed : 3.');
        }
        foreach ($honeypots as $honeypot) {
            if (Honeypot::where('dns', $honeypot['dns'])->exists()) {
                abort(500, "HoneyPots {$honeypot['dns']} already exists");
            }
            Honeypot::create([
                'dns' => $honeypot['dns'],
                'status' => HoneypotStatusesEnum::DNS_SETUP,
                'cloud_provider' => HoneypotCloudProvidersEnum::AWS,
                'cloud_sensor' => HoneypotCloudSensorsEnum::from($honeypot['sensor']),
            ]);
        }
        return Honeypot::query()
            ->orderBy('dns')
            ->get()
            ->toArray();
    }

    public function moveHoneypotsConfigurationToNextStep(): void
    {
        Honeypot::where('status', '!=', HoneypotStatusesEnum::SETUP_COMPLETE)
            ->get()
            ->each(function (Honeypot $honeypot) {

                $statuses = [
                    HoneypotStatusesEnum::DNS_SETUP,
                    HoneypotStatusesEnum::HONEYPOT_SETUP,
                    HoneypotStatusesEnum::SETUP_COMPLETE,
                ];

                $nextIdx = array_search($honeypot->status, $statuses) + 1;
                $honeypot->status = $statuses[$nextIdx];
                $honeypot->save();

                if ($statuses[$nextIdx] === HoneypotStatusesEnum::HONEYPOT_SETUP) {
                    /** @var User $user */
                    $user = Auth::user();
                    $subject = "Setup of honeypot {$honeypot->dns} requested";
                    $body = [
                        'id' => $honeypot->id,
                        'sensor' => $honeypot->cloud_sensor,
                        'provider' => $honeypot->cloud_provider,
                        'query' => "UPDATE honeypots SET status = 'setup_complete' WHERE id = {$honeypot->id};",
                    ];
                    Mail::to('support@computablefacts.freshdesk.com')->send(new HoneypotRequested($user, $subject, $body));
                }
            });
    }

    public function assetTags(): array
    {
        return [
            'tags' => AssetTag::query()
                ->get()
                ->pluck('tag')
                ->unique()
                ->toArray(),
        ];
    }

    public function getHashes(): array
    {
        return AssetTagHash::all()->toArray();
    }

    public function createHash(Request $request): AssetTagHash
    {
        $tag = $request->validate([
            'tag' => 'string|required',
        ])['tag'];
        return AssetTagHash::create([
            'tag' => $tag,
            'hash' => Str::random(32),
        ]);
    }

    public function deleteHash(AssetTagHash $hash): JsonResponse
    {
        $hash->delete();
        return response()->json(['message' => 'Hash successfully deleted']);
    }

    public function createHiddenAlert(Request $request): array
    {
        $payload = $request->validate([
            'uid' => 'nullable|string',
            'type' => 'nullable|string',
            'title' => 'nullable|string',
        ]);

        $uid = $request->string('uid');
        $type = $request->string('type');
        $title = $request->string('title');

        if (!$uid && !$type && !$title) {
            abort(500, 'At least one of uid, type or title must be present.');
        }

        $hiddenAlerts = HiddenAlert::query();

        if ($uid) {
            $hiddenAlerts->where('uid', $uid);
        } else if ($type) {
            $hiddenAlerts->where('type', $type);
        } else if ($title) {
            $hiddenAlerts->where('title', $title);
        }

        $hidden = $hiddenAlerts->first();

        if (!$hidden) {
            $hidden = HiddenAlert::create([
                'uid' => $uid,
                'type' => $type,
                'title' => $title,
            ]);
        }
        return $hidden;
    }

    public function deleteHiddenAlert(HiddenAlert $hiddenAlert): void
    {
        $hiddenAlert->delete();
    }
}