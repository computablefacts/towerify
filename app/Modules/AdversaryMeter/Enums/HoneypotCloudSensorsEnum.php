<?php

namespace App\Modules\AdversaryMeter\Enums;

enum HoneypotCloudSensorsEnum: string
{
    case HTTP = 'HTTP';
    case HTTPS = 'HTTPS';
    case SSH = 'SSH';
}