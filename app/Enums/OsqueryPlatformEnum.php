<?php

namespace App\Enums;

enum OsqueryPlatformEnum: string
{
    case DARWIN = 'darwin';
    case LINUX = 'linux';
    case POSIX = 'posix'; // DARWIN + LINUX
    case WINDOWS = 'windows';
    case UBUNTU = 'ubuntu';
    case CENTOS = 'centos';
    case ALL = 'all';
}