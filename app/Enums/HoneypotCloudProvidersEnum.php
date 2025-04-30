<?php

namespace App\Enums;

enum HoneypotCloudProvidersEnum: string
{
    case AWS = 'AWS';
    case AZURE = 'AZURE';
    case GCP = 'GCP';
}