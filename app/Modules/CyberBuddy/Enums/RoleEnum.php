<?php

namespace App\Modules\CyberBuddy\Enums;

enum RoleEnum: string
{
    case ASSISTANT = 'assistant';
    case DEVELOPER = 'developer';
    case SYSTEM = 'system';
    case TOOL = 'tool';
    case USER = 'user';
}
