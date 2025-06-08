<?php

namespace App\Providers;

use App\Listeners\SamlEventSubscriber;
use App\Models\Address;
use App\Models\Adjustment;
use App\Models\Asset;
use App\Models\AssetTag;
use App\Models\AssetTagHash;
use App\Models\Billpayer;
use App\Models\Carrier;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Channel;
use App\Models\Chunk;
use App\Models\ChunkTag;
use App\Models\Collection;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\File;
use App\Models\HiddenAlert;
use App\Models\Honeypot;
use App\Models\Invitation;
use App\Models\LinkGroup;
use App\Models\LinkGroupItem;
use App\Models\LinkType;
use App\Models\MasterProduct;
use App\Models\MasterProductVariant;
use App\Models\Media;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\PaymentMethod;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Prompt;
use App\Models\Property;
use App\Models\PropertyValue;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShippingMethod;
use App\Models\TaxCategory;
use App\Models\Taxon;
use App\Models\Taxonomy;
use App\Models\TaxRate;
use App\Models\Template;
use App\Models\YnhBackup;
use App\Models\YnhOverview;
use App\Models\YnhServer;
use App\Models\Zone;
use App\Models\ZoneMember;
use App\Observers\AddressObserver;
use App\Observers\AdjustmentObserver;
use App\Observers\AssetObserver;
use App\Observers\AssetTagHashObserver;
use App\Observers\AssetTagObserver;
use App\Observers\BillpayerObserver;
use App\Observers\CarrierObserver;
use App\Observers\CartItemObserver;
use App\Observers\CartObserver;
use App\Observers\ChannelObserver;
use App\Observers\ChunkObserver;
use App\Observers\ChunkTagObserver;
use App\Observers\CollectionObserver;
use App\Observers\ConversationObserver;
use App\Observers\CustomerObserver;
use App\Observers\FilesObserver;
use App\Observers\HiddenAlertObserver;
use App\Observers\HoneypotObserver;
use App\Observers\LinkGroupItemObserver;
use App\Observers\LinkGroupObserver;
use App\Observers\LinkTypeObserver;
use App\Observers\MasterProductObserver;
use App\Observers\MasterProductVariantObserver;
use App\Observers\MediaObserver;
use App\Observers\OrderItemObserver;
use App\Observers\OrderObserver;
use App\Observers\OrganizationObserver;
use App\Observers\PaymentHistoryObserver;
use App\Observers\PaymentMethodObserver;
use App\Observers\PaymentObserver;
use App\Observers\PermissionObserver;
use App\Observers\PersonObserver;
use App\Observers\ProductObserver;
use App\Observers\ProfileObserver;
use App\Observers\PromptObserver;
use App\Observers\PropertyObserver;
use App\Observers\PropertyValueObserver;
use App\Observers\RoleObserver;
use App\Observers\ShipmentObserver;
use App\Observers\ShippingMethodObserver;
use App\Observers\TaxCategoryObserver;
use App\Observers\TaxonObserver;
use App\Observers\TaxonomyObserver;
use App\Observers\TaxRateObserver;
use App\Observers\TemplateObserver;
use App\Observers\YnhBackupObserver;
use App\Observers\YnhServerObserver;
use App\Observers\YnhSummaryObserver;
use App\Observers\ZoneMemberObserver;
use App\Observers\ZoneObserver;
use App\Rules\AtLeastOneDigit;
use App\Rules\AtLeastOneLetter;
use App\Rules\AtLeastOneLowercaseLetter;
use App\Rules\AtLeastOneUppercaseLetter;
use App\Rules\OnlyLettersAndDigits;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // AdversaryMeter
        $this->app->bind('am_api_utils', function () {
            return new \App\Helpers\VulnerabilityScannerApiUtils();
        });

        // CyberBuddy
        $this->app->bind('cb_api_utils', function () {
            return new \App\Helpers\ApiUtils();
        });

        // Reports
        $this->app->bind('re_api_utils', function () {
            return new \App\Helpers\SupersetApiUtils();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->environment() == 'production') {
            $this->app['request']->server->set('HTTPS', true);
        }

        Password::defaults(
            Password::min(12)
                ->max(100)
                ->rules([
                    new OnlyLettersAndDigits,
                    new AtLeastOneLetter,
                    new AtLeastOneDigit,
                    new AtLeastOneUppercaseLetter,
                    new AtLeastOneLowercaseLetter,
                ])
        );

        $this->setSchemaDefaultLength();

        Validator::extend('base64image', function ($attribute, $value, $parameters, $validator) {
            $explode = explode(',', $value);
            $allow = ['png', 'jpg', 'svg', 'jpeg'];
            $format = str_replace(
                [
                    'data:image/',
                    ';',
                    'base64',
                ],
                [
                    '', '', '',
                ],
                $explode[0]
            );

            // check file format
            if (!in_array($format, $allow)) {
                return false;
            }

            // check base64 format
            if (!preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $explode[1])) {
                return false;
            }

            return true;
        });

        YnhBackup::observe(YnhBackupObserver::class);
        YnhServer::observe(YnhServerObserver::class);
        YnhOverview::observe(YnhSummaryObserver::class);

        // AdversaryMeter
        Asset::observe(AssetObserver::class);
        AssetTagHash::observe(AssetTagHashObserver::class);
        AssetTag::observe(AssetTagObserver::class);
        HiddenAlert::observe(HiddenAlertObserver::class);
        Honeypot::observe(HoneypotObserver::class);

        // CyberBuddy
        Chunk::observe(ChunkObserver::class);
        ChunkTag::observe(ChunkTagObserver::class);
        Collection::observe(CollectionObserver::class);
        Conversation::observe(ConversationObserver::class);
        File::observe(FilesObserver::class);
        Prompt::observe(PromptObserver::class);
        Template::observe(TemplateObserver::class);

        // SAML
        Event::subscribe(SamlEventSubscriber::class);
    }

    private function setSchemaDefaultLength(): void
    {
        try {
            Schema::defaultStringLength(191);
        } catch (\Exception $exception) {
        }
    }
}
