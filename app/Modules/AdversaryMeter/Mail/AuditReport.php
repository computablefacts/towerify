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

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Collection $alertsHigh, Collection $alertsMedium, Collection $alertsLow, Collection $assetsMonitored, Collection $assetsNotMonitored)
    {
        $this->alertsHigh = $alertsHigh;
        $this->alertsMedium = $alertsMedium;
        $this->alertsLow = $alertsLow;
        $this->assetsMonitored = $assetsMonitored;
        $this->assetsNotMonitored = $assetsNotMonitored;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // TODO : préfixer le sujet avec [INFO] ou [ACTION] si présence de vulnérabilités high
        return $this
            ->from('support@computablefacts.freshdesk.com', 'Support')
            ->subject("Cywise : Rapport d'audit")
            ->markdown('modules.adversary-meter.email.audit-report', [
                "alerts_high" => $this->alertsHigh,
                "alerts_medium" => $this->alertsMedium,
                "alerts_low" => $this->alertsLow,
                "assets_monitored" => $this->assetsMonitored,
                "assets_not_monitored" => $this->assetsNotMonitored,
            ]);
    }
}
