<?php

namespace App\Enums;

enum AssetTypesEnum: string
{
    case DNS = 'DNS';
    case IP = 'IP';
    case RANGE = 'RANGE';
}