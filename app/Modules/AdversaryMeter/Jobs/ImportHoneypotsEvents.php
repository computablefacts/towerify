<?php

namespace App\Modules\AdversaryMeter\Jobs;

use App\Modules\AdversaryMeter\Enums\HoneypotStatusesEnum;
use App\Modules\AdversaryMeter\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\AdversaryMeter\Models\Attacker;
use App\Modules\AdversaryMeter\Models\Honeypot;
use App\Modules\AdversaryMeter\Models\HoneypotEvent;
use App\Modules\AdversaryMeter\Traits\AttackerNameGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportHoneypotsEvents implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AttackerNameGenerator;

    public $tries = 1;
    public $maxExceptions = 1;
    public $timeout = 15 * 60; // 15mn
    private array $cache = [];

    public function __construct()
    {
        //
    }

    public function handle()
    {
        $this->events()->each(function (array $data) {

            $file = $data['file'];
            $events = $data['events'];

            if (count($events) <= 0) {
                $this->trash($file);
                return;
            }

            $dns = basename(dirname($file));
            $honeypot = Honeypot::where('dns', $dns)->first();

            if ($honeypot) {

                $feed = basename($file);

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

                $this->archive($file);
            }
        });
    }

    private function events(): Collection
    {
        $honeypots = Honeypot::where('status', HoneypotStatusesEnum::SETUP_COMPLETE)
            ->get()
            ->map(fn(Honeypot $honeypot) => $honeypot->dns)
            ->values();
        return collect($this->directories())->filter(function ($dir) use ($honeypots) {
            return $honeypots->contains($dir);
        })->flatMap(function (string $dir) {
            return $this->files($dir);
        })->map(function (string $file) {
            $content = $this->disk()->get($file);
            $json = json_decode($content, true);
            return $json ? [
                'file' => $file,
                'events' => $json,
            ] : [];
        })->filter(fn(array $obj) => count($obj) > 0);
    }

    private function trash(string $file): void
    {
        $this->disk()->delete($file);
    }

    private function archive(string $file): void
    {
        $destination = dirname($file) . '_out/' . basename($file);
        $this->disk()->move($file, $destination);
    }

    private function files($dir): array
    {
        return $this->disk()->files($dir);
    }

    private function directories(): array
    {
        return $this->disk()->directories();
    }

    private function disk(): Filesystem
    {
        return Storage::disk('honeypots-s3');
    }

    private function hostingProvider(string $ip): array
    {
        if (!isset($this->cache[$ip])) {
            $this->cache[$ip] = ApiUtils::ip_whois_public($ip);
        }
        return $this->cache[$ip];
    }
}
