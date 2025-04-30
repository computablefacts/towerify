<?php

namespace App\Listeners;

use App\Events\IngestHoneypotsEvents;
use App\Helpers\VulnerabilityScannerApiUtilsFacade as ApiUtils;
use App\Models\Attacker;
use App\Models\Honeypot;
use App\Models\HoneypotEvent;
use App\Traits\AttackerNameGenerator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IngestHoneypotsEventsListener extends AbstractListener
{
    use AttackerNameGenerator;

    private array $cache = [];

    public function viaQueue(): string
    {
        return self::LOW;
    }

    protected function handle2($event)
    {
        if (!($event instanceof IngestHoneypotsEvents)) {
            throw new \Exception('Invalid event type!');
        }

        $timestamp = $event->timestamp;
        $dns = $event->dns;
        $events = $event->events;
        $nbEvents = count($events);
        $honeypot = Honeypot::where('dns', $dns)->first();

        if (!$honeypot) {
            Log::error("Honeypot {$dns} not found! {$nbEvents} events dropped.");
        } else {

            $feed = "teler-{$dns}-access.{$timestamp->format('Y.m.d_H.i.s')}.json"; // for backward compatibility

            foreach ($events as $event) {
                $hp = $this->hostingProvider($event['ip']);
                /** @var HoneypotEvent $e */
                $e = HoneypotEvent::create([
                    'honeypot_id' => $honeypot->id,
                    'event' => $event['event'],
                    'uid' => $event['uid'] ?? '',
                    'human' => $event['human'] == '1',
                    'endpoint' => $event['endpoint'] ?? '',
                    'timestamp' => $event['timestamp'],
                    'request_uri' => Str::limit(trim($event['request_uri'] ?? '', 191)),
                    'user_agent' => Str::limit(trim($event['user_agent'] ?? '', 191)),
                    'ip' => $event['ip'],
                    'details' => Str::limit(trim($event['details'] ?? '', 191)),
                    'targeted' => $event['targeted'] == '1',
                    'feed_name' => $feed,
                    'hosting_service_description' => $hp['data']['asn_description'] ?? null,
                    'hosting_service_registry' => $hp['data']['asn_registry'] ?? null,
                    'hosting_service_asn' => $hp['data']['asn'] ?? null,
                    'hosting_service_cidr' => $hp['data']['asn_cidr'] ?? null,
                    'hosting_service_country_code' => $hp['data']['asn_country_code'] ?? null,
                    'hosting_service_date' => $hp['data']['asn_date'] ?? null,
                ]);
                if ($e->targeted || $e->human) {

                    /** @var HoneypotEvent $eventWithSameUid */
                    $eventWithSameUid = HoneypotEvent::where('uid', $event['uid'] ?? 'PLACEHOLDER')
                        ->whereNotNull('attacker_id')
                        ->first();
                    /** @var HoneypotEvent $eventWithSameIp */
                    $eventWithSameIp = HoneypotEvent::where('ip', $event['ip'] ?? 'PLACEHOLDER')
                        ->whereNotNull('attacker_id')
                        ->first();

                    $attackerWithSameUid = $eventWithSameUid?->attacker_id;
                    $attackerWithSameIp = $eventWithSameIp?->attacker_id;

                    if (!$attackerWithSameUid && !$attackerWithSameIp) {

                        // Create a new attacker's profile
                        $attacker = Attacker::create([
                            'name' => Str::upper($this->newCodename()),
                            'first_contact' => $e->timestamp,
                            'last_contact' => $e->timestamp,
                        ]);

                        // Update the history!
                        if (Str::startsWith($e->event, "ssh_bruteforce")) {
                            HoneypotEvent::where('ip', $e->ip)
                                ->whereNull('attacker_id')
                                ->update(['attacker_id' => $attacker->id]);
                        } else {
                            HoneypotEvent::where('uid', $e->uid)
                                ->whereNull('attacker_id')
                                ->update(['attacker_id' => $attacker->id]);
                        }

                        // Update the attacker's profile (events are processed in an undefined order...)
                        $firstContact = HoneypotEvent::where('attacker_id', $attacker->id)->min('timestamp');
                        $lastContact = HoneypotEvent::where('attacker_id', $attacker->id)->max('timestamp');

                        $attacker->first_contact = $firstContact;
                        $attacker->last_contact = $lastContact;
                        $attacker->save();
                    } else {
                        if ($attackerWithSameUid && $attackerWithSameIp) {
                            if (Str::startsWith($e->event, "ssh_bruteforce")) {
                                /** @var Attacker $attacker */
                                $attacker = Attacker::find($attackerWithSameIp);
                            } else {
                                /** @var Attacker $attacker */
                                $attacker = Attacker::find($attackerWithSameUid);
                            }
                        } elseif ($attackerWithSameUid) {
                            /** @var Attacker $attacker */
                            $attacker = Attacker::find($attackerWithSameUid);
                        } else {
                            /** @var Attacker $attacker */
                            $attacker = Attacker::find($attackerWithSameIp);
                        }

                        // Update the attacker's profile
                        if ($attacker->first_contact > $e->timestamp) {
                            $attacker->first_contact = $e->timestamp;
                        }
                        if ($attacker->last_contact < $e->timestamp) {
                            $attacker->last_contact = $e->timestamp;
                        }
                        $attacker->save();
                    }

                    // At last, update the event
                    $e->attacker_id = $attacker->id;
                    $e->save();
                }
            }
        }
    }

    private function hostingProvider(string $ip): array
    {
        try {
            if (!isset($this->cache[$ip])) {
                $this->cache[$ip] = ApiUtils::ip_whois_public($ip);
            }
            return $this->cache[$ip];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return [];
        }
    }
}
