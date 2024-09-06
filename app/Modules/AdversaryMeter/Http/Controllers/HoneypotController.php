<?php

namespace App\Modules\AdversaryMeter\Http\Controllers;

use App\Modules\AdversaryMeter\Models\Alert;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\Attacker;
use App\Modules\AdversaryMeter\Models\Honeypot;
use App\Modules\AdversaryMeter\Models\HoneypotEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HoneypotController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function attackerIndex(Request $request): array
    {
        $nbEvents = HoneypotEvent::count();
        return Attacker::select('attackers.*')
            ->orderBy('attackers.name')
            ->orderBy('attackers.last_contact')
            ->get()
            ->map(function (Attacker $attacker) use ($nbEvents) {

                $ips = HoneypotEvent::where('attacker_id', $attacker->id)
                    ->get()
                    ->map(fn(HoneypotEvent $event) => $event->ip)
                    ->toArray();

                $nbAttackerEvents = HoneypotEvent::where('attacker_id', $attacker->id)->count();
                $ratio = $nbAttackerEvents / $nbEvents * 100;

                if ($ratio <= 33) {
                    $aggressiveness = 'low';
                } elseif ($ratio <= 66) {
                    $aggressiveness = 'medium';
                } else {
                    $aggressiveness = 'high';
                }
                return [
                    'id' => $attacker->id,
                    'name' => $attacker->name,
                    'first_contact' => $attacker->first_contact,
                    'last_contact' => $attacker->last_contact,
                    'aggressiveness' => $aggressiveness,
                    'ips' => $ips,
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
            ->join('assets', 'assets.cur_scan_id', '=', 'ports.ports_scan_id')
            ->get()
            ->map(function (Alert $alert) use ($attackerId) {
                return [
                    'alert' => $alert,
                    'asset' => Asset::find($alert->asset_id),
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
                    'asset' => Asset::find($alert->asset_id),
                    'events' => $alert->events()->get()->toArray(),
                ];
            })
            ->toArray();
    }

    public function attackerActivity(Attacker $attacker): array
    {
        $events = $attacker->events()->orderBy('timestamp', 'desc')->get();
        return [
            'firstEvent' => $events->map(fn(HoneypotEvent $event) => $event->timestamp)->last(),
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
        $nbEvents = HoneypotEvent::count();
        $nbAttackerEvents = $attacker->events()->count();
        $ratio = $nbAttackerEvents / $nbEvents * 100;

        if ($ratio <= 33) {
            $aggressiveness = 'low';
        } elseif ($ratio <= 66) {
            $aggressiveness = 'medium';
        } else {
            $aggressiveness = 'high';
        }
        return [
            'id' => $attacker->id,
            'name' => $attacker->name,
            'first_contact' => $attacker->first_contact,
            'last_contact' => $attacker->last_contact,
            'count' => $nbAttackerEvents,
            'tot' => $nbEvents,
            'aggressiveness' => $aggressiveness,
        ];
    }

    public function attackerStats(Attacker $attacker): array
    {
        return [
            'attacks' => $attacker->events()->count(),
            'human' => $attacker->events()->where('human', true)->count(),
            'targeted' => $attacker->events()->where('targeted', true)->count(),
            'cve' => $attacker->events()->where('event', 'cve_tested')->count(),
        ];
    }

    public function getMostRecentEvent(?int $attackerId = null): array
    {
        if ($attackerId) {
            return HoneypotEvent::query()
                ->orderBy('timestamp', 'desc')
                ->limit(3)
                ->get()
                ->toArray();
        }
        return Attacker::find($attackerId)
            ->events()
            ->orderBy('timestamp', 'desc')
            ->limit(3)
            ->get()
            ->toArray();
    }

    public function attackerTools(Attacker $attacker): array
    {
        return $attacker->events()
            ->get()
            ->pluck('details')
            ->unique()
            ->toArray();
    }

    public function attackerCompetency(Attacker $attacker): array
    {
        $tools = $attacker->events()->where('event', 'tool_detected')->count();
        $cves = $attacker->events()->where('event', 'cve_tested')->count();
        $humans = $attacker->events()->where('human', true)->count();
        $ips = $attacker->events()->get()->pluck('ip')->unique()->count();
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

    public function getHoneypotEventStats(Honeypot $honeypot): array
    {
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
}