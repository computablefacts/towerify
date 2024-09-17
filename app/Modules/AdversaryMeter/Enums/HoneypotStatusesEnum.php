<?php

namespace App\Modules\AdversaryMeter\Enums;

enum HoneypotStatusesEnum: string
{
    case DNS_SETUP = 'dns_setup';
    case HONEYPOT_SETUP = 'honeypot_setup';
    case SETUP_COMPLETE = 'setup_complete';
}