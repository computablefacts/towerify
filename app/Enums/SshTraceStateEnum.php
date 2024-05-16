<?php

namespace App\Enums;

enum SshTraceStateEnum: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';
    case ERRORED = 'errored';
}