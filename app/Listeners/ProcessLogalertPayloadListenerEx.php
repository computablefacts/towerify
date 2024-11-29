<?php

namespace App\Listeners;

class ProcessLogalertPayloadListenerEx extends ProcessLogalertPayloadListener
{
    public function viaQueue(): string
    {
        return self::LOW;
    }
}
