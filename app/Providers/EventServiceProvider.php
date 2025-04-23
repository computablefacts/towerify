<?php

namespace App\Providers;

use App\Events\AddTwrUserPermission;
use App\Events\AddUserPermission;
use App\Events\BeginPortsScan;
use App\Events\BeginVulnsScan;
use App\Events\ConfigureHost;
use App\Events\CreateAsset;
use App\Events\CreateBackup;
use App\Events\DeleteAsset;
use App\Events\EndPortsScan;
use App\Events\EndVulnsScan;
use App\Events\ImportTable;
use App\Events\ImportVirtualTable;
use App\Events\IngestFile;
use App\Events\IngestHoneypotsEvents;
use App\Events\InstallApp;
use App\Events\ProcessLogalertPayload;
use App\Events\ProcessLogalertPayloadEx;
use App\Events\ProcessLogparserPayload;
use App\Events\PullServerInfos;
use App\Events\RebuildLatestEventsCache;
use App\Events\RebuildPackagesList;
use App\Events\RemoveUserPermission;
use App\Events\SendAuditReport;
use App\Events\SendInvitation;
use App\Events\UninstallApp;
use App\Listeners\AddTwrUserPermissionListener;
use App\Listeners\AddUserPermissionListener;
use App\Listeners\BeginPortsScanListener;
use App\Listeners\BeginVulnsScanListener;
use App\Listeners\ConfigureHostListener;
use App\Listeners\CreateAssetListener;
use App\Listeners\CreateBackupListener;
use App\Listeners\DeleteAssetListener;
use App\Listeners\EndPortsScanListener;
use App\Listeners\EndVulnsScanListener;
use App\Listeners\ImportTableListener;
use App\Listeners\ImportVirtualTableListener;
use App\Listeners\IngestFileListener;
use App\Listeners\IngestHoneypotsEventsListener;
use App\Listeners\InstallAppListener;
use App\Listeners\OrderCreatedListener;
use App\Listeners\PasswordResetListener;
use App\Listeners\ProcessLogalertPayloadListener;
use App\Listeners\ProcessLogalertPayloadListenerEx;
use App\Listeners\ProcessLogparserPayloadListener;
use App\Listeners\RebuildLatestEventsCacheListener;
use App\Listeners\RebuildPackagesListListener;
use App\Listeners\RemoveUserPermissionListener;
use App\Listeners\SendAuditReportListener;
use App\Listeners\SendInvitationListener;
use App\Listeners\StripeEventListener;
use App\Listeners\UninstallAppListener;
use App\Listeners\UpdateServerInfosListener;
use App\Listeners\UserInvitationUtilizedListener;
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
        ProcessLogalertPayloadEx::class => [
            ProcessLogalertPayloadListenerEx::class,
        ],
        ProcessLogparserPayload::class => [
            ProcessLogparserPayloadListener::class,
        ],
        RebuildPackagesList::class => [
            RebuildPackagesListListener::class,
        ],
        RebuildLatestEventsCache::class => [
            RebuildLatestEventsCacheListener::class,
        ],
        SendInvitation::class => [
            SendInvitationListener::class
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
        IngestHoneypotsEvents::class => [
            IngestHoneypotsEventsListener::class,
        ],
        SendAuditReport::class => [
            SendAuditReportListener::class,
        ],

        // CyberBuddy
        IngestFile::class => [
            IngestFileListener::class,
        ],
        ImportTable::class => [
            ImportTableListener::class,
        ],
        ImportVirtualTable::class => [
            ImportVirtualTableListener::class,
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
