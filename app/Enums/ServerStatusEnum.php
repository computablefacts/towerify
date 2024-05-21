<?php

namespace App\Enums;

enum ServerStatusEnum: string
{
    case RUNNING = 'running';
    case UNKNOWN = 'unknown';
    case DOWN = 'down';
}