<?php

namespace App\Providers;

use App\Events\AddTwrUserPermission;
use App\Events\AddUserPermission;
use App\Events\ConfigureHost;
use App\Events\CreateBackup;
use App\Events\InstallApp;
use App\Events\ProcessLogalertPayload;
use App\Events\ProcessLogparserPayload;
use App\Events\PullServerInfos;
use App\Events\RemoveUserPermission;
use App\Events\UninstallApp;
use App\Listeners\AddTwrUserPermissionListener;
use App\Listeners\AddUserPermissionListener;
use App\Listeners\ConfigureHostListener;
use App\Listeners\CreateBackupListener;
use App\Listeners\InstallAppListener;
use App\Listeners\OrderCreatedListener;
use App\Listeners\PasswordResetListener;
use App\Listeners\ProcessLogalertPayloadListener;
use App\Listeners\ProcessLogparserPayloadListener;
use App\Listeners\RemoveUserPermissionListener;
use App\Listeners\StripeEventListener;
use App\Listeners\UninstallAppListener;
use App\Listeners\UpdateServerInfosListener;
use App\Listeners\UserInvitationUtilizedListener;
use App\Modules\AdversaryMeter\Events\BeginPortsScan;
use App\Modules\AdversaryMeter\Events\BeginVulnsScan;
use App\Modules\AdversaryMeter\Events\CreateAsset;
use App\Modules\AdversaryMeter\Events\DeleteAsset;
use App\Modules\AdversaryMeter\Events\EndPortsScan;
use App\Modules\AdversaryMeter\Events\EndVulnsScan;
use App\Modules\AdversaryMeter\Listeners\BeginPortsScanListener;
use App\Modules\AdversaryMeter\Listeners\BeginVulnsScanListener;
use App\Modules\AdversaryMeter\Listeners\CreateAssetListener;
use App\Modules\AdversaryMeter\Listeners\DeleteAssetListener;
use App\Modules\AdversaryMeter\Listeners\EndPortsScanListener;
use App\Modules\AdversaryMeter\Listeners\EndVulnsScanListener;
use App\Modules\CyberBuddy\Events\IngestFile;
use App\Modules\CyberBuddy\Listeners\IngestFileListener;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Konekt\User\Events\UserInvitationUtilized;
use Laravel\Cashier\Events\WebhookReceived;
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
        PasswordReset::class => [
            PasswordResetListener::class,
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
        ProcessLogalertPayload::class => [
            ProcessLogalertPayloadListener::class,
        ],
        ProcessLogparserPayload::class => [
            ProcessLogparserPayloadListener::class,
        ],

        // AdversaryMeter
        BeginPortsScan::class => [
            BeginPortsScanListener::class,
        ],
        EndPortsScan::class => [
            EndPortsScanListener::class,
        ],
        BeginVulnsScan::class => [
            BeginVulnsScanListener::class,
        ],
        EndVulnsScan::class => [
            EndVulnsScanListener::class,
        ],
        CreateAsset::class => [
            CreateAssetListener::class,
        ],
        DeleteAsset::class => [
            DeleteAssetListener::class,
        ],

        // CyberBuddy
        IngestFile::class => [
            IngestFileListener::class,
        ],

        // Stripe
        WebhookReceived::class => [
            StripeEventListener::class,
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
