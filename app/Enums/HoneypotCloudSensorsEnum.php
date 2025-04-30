<?php

namespace App\Enums;

enum HoneypotCloudSensorsEnum: string
{
    case HTTP = 'HTTP';
    case HTTPS = 'HTTPS';
    case SSH = 'SSH';
}