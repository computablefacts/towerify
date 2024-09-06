<?php

namespace App\Modules\AdversaryMeter\Http\Controllers;

use App\Modules\AdversaryMeter\Models\Attacker;
use App\Modules\AdversaryMeter\Models\HoneypotEvent;
use Illuminate\Http\Request;

class HoneypotController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function attackerIndex(Request $request)
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

                $nbAttackerEvents = HoneypotEvent::where('attacker_id', $attacker->id)
                    ->count();

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
}