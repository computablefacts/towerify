<?php

namespace App\Providers;

use App\Events\AddTwrUserPermission;
use App\Events\AddUserPermission;
use App\Events\ConfigureHost;
use App\Events\CreateBackup;
use App\Events\InstallApp;
use App\Events\RemoveUserPermission;
use App\Events\UninstallApp;
use App\Events\PullServerInfos;
use App\Listeners\AddTwrUserPermissionListener;
use App\Listeners\AddUserPermissionListener;
use App\Listeners\ConfigureHostListener;
use App\Listeners\CreateBackupListener;
use App\Listeners\InstallAppListener;
use App\Listeners\OrderCreatedListener;
use App\Listeners\RemoveUserPermissionListener;
use App\Listeners\UninstallAppListener;
use App\Listeners\UpdateServerInfosListener;
use App\Listeners\UserInvitationUtilizedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Konekt\User\Events\UserInvitationUtilized;
use Vanilo\Order\Events\OrderWasCreated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
        ConfigureHost::class => [
            ConfigureHostListener::class,
        ],
        InstallApp::class => [
            InstallAppListener::class,
        ],
        UninstallApp::class => [
            UninstallAppListener::class,
        ],
        AddUserPermission::class => [
            AddUserPermissionListener::class,
        ],
        AddTwrUserPermission::class => [
            AddTwrUserPermissionListener::class,
        ],
        RemoveUserPermission::class => [
            RemoveUserPermissionListener::class,
        ],
        OrderWasCreated::class => [
            OrderCreatedListener::class,
        ],
        UserInvitationUtilized::class => [
            UserInvitationUtilizedListener::class,
        ],
        CreateBackup::class => [
            CreateBackupListener::class,
        ],
        PullServerInfos::class => [
            UpdateServerInfosListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
