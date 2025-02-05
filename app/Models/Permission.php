<?php

namespace App\Models;

use Konekt\Acl\Models\Permission as PermissionBase;

class Permission extends PermissionBase
{
    /** @deprecated */
    const LIST_SERVERS = 'list servers';
    /** @deprecated */
    const LIST_APPS = 'list apps';
    /** @deprecated */
    const LIST_USERS = 'list end users';
    /** @deprecated */
    const MANAGE_SERVERS = 'manage servers';
    /** @deprecated */
    const MANAGE_APPS = 'manage apps';
    /** @deprecated */
    const MANAGE_USERS = 'manage end users';
    /** @deprecated */
    const BUY_STUFF = 'buy stuff'; // display store & cart
    /** @deprecated */
    const USE_ADVERSARY_METER = 'use adversary meter';
    /** @deprecated */
    const USE_HONEYPOTS = 'use honeypots';
    /** @deprecated */
    const USE_VULNERABILITY_SCANNER = 'use vulnerability scanner';
    /** @deprecated */
    const USE_AGENTS = 'use agents';
    /** @deprecated */
    const USE_CYBER_BUDDY = 'use cyber buddy';
    /** @deprecated */
    const USE_YUNOHOST = 'use yunohost';
    /** @deprecated */
    const USE_MARKETPLACE = 'use marketplace';

    const VIEW_HOME = 'view home';
    const VIEW_OVERVIEW = 'view overview';
    const VIEW_METRICS = 'view metrics';
    const VIEW_EVENTS = 'view events';

    const VIEW_VULNERABILITY_SCANNER = 'view vulnerability scanner';
    const VIEW_ASSETS = 'view assets';
    const VIEW_VULNERABILITIES = 'view vulnerabilities';
    const VIEW_SERVICE_PROVIDER_DELEGATION = 'view service provider delegation';

    const VIEW_AGENTS = 'view agents';
    const VIEW_SECURITY_RULES = 'view security rules';

    const VIEW_HONEYPOTS = 'view honeypots';
    const VIEW_ATTACKERS = 'view attackers';
    const VIEW_IP_BLACKLIST = 'view ip blacklist';

    const VIEW_ISSP = 'view issp';
    const VIEW_HARDENING = 'view hardening';
    const VIEW_FRAMEWORKS = 'view frameworks';
    const VIEW_AI_WRITER = 'view ai writer';
    const VIEW_CYBERBUDDY = 'view cyberbuddy';
    const VIEW_CONVERSATIONS = 'view conversations';
    const VIEW_COLLECTIONS = 'view collections';
    const VIEW_DOCUMENTS = 'view documents';
    const VIEW_TABLES = 'view tables';
    const VIEW_CHUNKS = 'view chunks';
    const VIEW_PROMPTS = 'view prompts';

    const VIEW_YUNOHOST = 'view yunohost';
    const VIEW_DESKTOP = 'view desktop';
    const VIEW_SERVERS = 'view servers';
    const VIEW_APPLICATIONS = 'view applications';
    const VIEW_DOMAINS = 'view domains';
    const VIEW_BACKUPS = 'view backups';
    const VIEW_INTERDEPENDENCIES = 'view interdependencies';
    const VIEW_TRACES = 'view traces';

    const VIEW_MARKETPLACE = 'view marketplace';
    const VIEW_PRODUCTS = 'view products';
    const VIEW_CART = 'view cart';
    const VIEW_ORDERS = 'view orders';

    const VIEW_SETTINGS = 'view settings';
    const VIEW_USERS = 'view users';
    const VIEW_INVITATIONS = 'view invitations';
    const VIEW_PLANS = 'view plans';
    const VIEW_MY_SUBSCRIPTION = 'view my subscription';
    const VIEW_DOCUMENTATION = 'view documentation';
    const VIEW_TERMS = 'view terms';
    const VIEW_RESET_PASSWORD = 'view reset password';
}