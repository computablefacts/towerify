<?php

namespace App\Models;

use Konekt\Acl\Models\Role as RoleBase;

class Role extends RoleBase
{
    const ADMIN = 'admin';
    const ADMINISTRATOR = 'administrator';
    const LIMITED_ADMINISTRATOR = 'limited administrator';
    const BASIC_END_USER = 'basic end user';
    const CYBERBUDDY_ONLY = 'cyberbuddy only';
    const ROLES = [
        self::ADMIN => [

            // Hardware
            Permission::LIST_SERVERS,
            Permission::MANAGE_SERVERS,
            Permission::USE_AGENTS,

            // Software
            Permission::LIST_APPS,
            Permission::MANAGE_APPS,
            Permission::USE_YUNOHOST,

            // Users
            Permission::LIST_USERS,
            Permission::MANAGE_USERS,

            // Access to store and cart
            Permission::BUY_STUFF,
            Permission::USE_MARKETPLACE,

            // Access to AdversaryMeter
            Permission::USE_ADVERSARY_METER,
            Permission::USE_VULNERABILITY_SCANNER,
            Permission::USE_HONEYPOTS,

            // Access to CyberBuddy
            Permission::USE_CYBER_BUDDY,
        ],
        self::ADMINISTRATOR => [

            // Hardware
            Permission::LIST_SERVERS,
            Permission::MANAGE_SERVERS,
            Permission::USE_AGENTS,

            // Software
            Permission::LIST_APPS,
            Permission::MANAGE_APPS,
            Permission::USE_YUNOHOST,

            // Users
            Permission::LIST_USERS,
            Permission::MANAGE_USERS,

            // Access to store and cart
            // Permission::BUY_STUFF,
            // Permission::USE_MARKETPLACE,

            // Access to AdversaryMeter
            Permission::USE_ADVERSARY_METER,
            Permission::USE_VULNERABILITY_SCANNER,
            Permission::USE_HONEYPOTS,

            // Access to CyberBuddy
            Permission::USE_CYBER_BUDDY,

            // Add missing permissions after upgrade to Laravel 11.0+ and Vanilo 4.0+
            'list tax rates',
            'view tax rates',
            'list tax categories',
            'view tax categories',
        ],
        self::LIMITED_ADMINISTRATOR => [

            // Hardware
            Permission::LIST_SERVERS,

            // Software
            Permission::LIST_APPS,
            Permission::MANAGE_APPS,
            Permission::USE_YUNOHOST,

            // Users
            Permission::LIST_USERS,
            Permission::MANAGE_USERS,

            // Access to store and cart
            // Permission::BUY_STUFF,
            // Permission::USE_MARKETPLACE,

            // Access to AdversaryMeter
            Permission::USE_ADVERSARY_METER,
            Permission::USE_VULNERABILITY_SCANNER,
            Permission::USE_HONEYPOTS,

            // Access to CyberBuddy
            Permission::USE_CYBER_BUDDY,
        ],
        self::BASIC_END_USER => [
            Permission::LIST_APPS,
        ],
        self::CYBERBUDDY_ONLY => [
            Permission::USE_CYBER_BUDDY,
        ],
    ];
}