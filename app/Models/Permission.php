<?php

namespace App\Models;

use Konekt\Acl\Models\Permission as PermissionBase;

class Permission extends PermissionBase
{
    const LIST_SERVERS = 'list servers';
    const LIST_APPS = 'list apps';
    const LIST_USERS = 'list end users';

    const MANAGE_SERVERS = 'manage servers';
    const MANAGE_APPS = 'manage apps';
    const MANAGE_USERS = 'manage end users';

    const BUY_STUFF = 'buy stuff'; // display store & cart
    const USE_ADVERSARY_METER = 'use adversary meter';
}