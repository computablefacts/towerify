<?php

namespace App\Modules\AdversaryMeter\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AuditReport extends Mailable
{
    use Queueable, SerializesModels;

    private Collection $alertsHigh;
    private Collection $alertsMedium;
    private Collection $alertsLow;
    private Collection $assetsMonitored;
    private Collection $assetsNotMonitored;
    private Collection $assetsDiscovered;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Collection $alertsHigh, Collection $alertsMedium, Collection $alertsLow, Collection $assetsMonitored, Collection $assetsNotMonitored, Collection $assetsDiscovered)
    {
        $this->alertsHigh = $alertsHigh;
        $this->alertsMedium = $alertsMedium;
        $this->alertsLow = $alertsLow;
        $this->assetsMonitored = $assetsMonitored;
        $this->assetsNotMonitored = $assetsNotMonitored;
        $this->assetsDiscovered = $assetsDiscovered;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $events = '';
        if ($this->alertsHigh->count() > 0) {
            if ($this->alertsHigh->count() === 1) {
                $events .= "{$this->alertsHigh->count()} vulnérabilité critique";
            } else {
                $events .= "{$this->alertsHigh->count()} vulnérabilités critiques";
            }
        }
        if ($this->assetsDiscovered->count() > 0) {
            if (!empty($events)) {
                $events .= ", ";
            }
            if ($this->assetsDiscovered->count() === 1) {
                $events .= "{$this->assetsDiscovered->count()} nouvel actif découvert";
            } else {
                $events .= "{$this->assetsDiscovered->count()} nouveaux actifs découverts";
            }
        }
        $events = empty($events) ? '' : "({$events})";
        return $this
            ->from('support@computablefacts.freshdesk.com', 'Support')
            ->subject("Cywise : Rapport d'audit {$events}")
            ->markdown('modules.adversary-meter.email.audit-report', [
                "alerts_high" => $this->alertsHigh,
                "alerts_medium" => $this->alertsMedium,
                "alerts_low" => $this->alertsLow,
                "assets_monitored" => $this->assetsMonitored,
                "assets_not_monitored" => $this->assetsNotMonitored,
                "assets_discovered" => $this->assetsDiscovered,
            ]);
    }
}
