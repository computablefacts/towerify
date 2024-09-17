<?php

namespace App\Providers;

use App\Hashing\TwHasher;
use App\Models\Address;
use App\Models\Adjustment;
use App\Models\Billpayer;
use App\Models\Carrier;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Channel;
use App\Models\Customer;
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
use App\Models\Property;
use App\Models\PropertyValue;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShippingMethod;
use App\Models\TaxCategory;
use App\Models\Taxon;
use App\Models\Taxonomy;
use App\Models\TaxRate;
use App\Models\YnhBackup;
use App\Models\YnhServer;
use App\Models\Zone;
use App\Models\ZoneMember;
use App\Modules\AdversaryMeter\Helpers\ApiUtils;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\AssetTag;
use App\Modules\AdversaryMeter\Models\AssetTagHash;
use App\Modules\AdversaryMeter\Models\Honeypot;
use App\Modules\AdversaryMeter\Observers\AssetObserver;
use App\Modules\AdversaryMeter\Observers\AssetTagHashObserver;
use App\Modules\AdversaryMeter\Observers\AssetTagObserver;
use App\Modules\AdversaryMeter\Observers\HoneypotObserver;
use App\Observers\AddressObserver;
use App\Observers\AdjustmentObserver;
use App\Observers\BillpayerObserver;
use App\Observers\CarrierObserver;
use App\Observers\CartItemObserver;
use App\Observers\CartObserver;
use App\Observers\ChannelObserver;
use App\Observers\CustomerObserver;
use App\Observers\InvitationObserver;
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
use App\Observers\PropertyObserver;
use App\Observers\PropertyValueObserver;
use App\Observers\RoleObserver;
use App\Observers\ShipmentObserver;
use App\Observers\ShippingMethodObserver;
use App\Observers\TaxCategoryObserver;
use App\Observers\TaxonObserver;
use App\Observers\TaxonomyObserver;
use App\Observers\TaxRateObserver;
use App\Observers\YnhBackupObserver;
use App\Observers\YnhServerObserver;
use App\Observers\ZoneMemberObserver;
use App\Observers\ZoneObserver;
use App\Rules\AtLeastOneDigit;
use App\Rules\AtLeastOneLetter;
use App\Rules\AtLeastOneLowercaseLetter;
use App\Rules\AtLeastOneUppercaseLetter;
use App\Rules\OnlyLettersAndDigits;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        if (Str::startsWith(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Hash::extend('tw_hasher', static function () {
            return new TwHasher();
        });

        Password::defaults(Password::min(12)->max(100)->rules([
            new OnlyLettersAndDigits,
            new AtLeastOneLetter,
            new AtLeastOneDigit,
            new AtLeastOneUppercaseLetter,
            new AtLeastOneLowercaseLetter,
        ]));

        $this->app->concord->registerModel(\Konekt\Address\Contracts\Address::class, Address::class);
        $this->app->concord->registerModel(\Vanilo\Adjustments\Contracts\Adjustment::class, Adjustment::class);
        $this->app->concord->registerModel(\Vanilo\Order\Contracts\Billpayer::class, Billpayer::class);
        $this->app->concord->registerModel(\Vanilo\Shipment\Contracts\Carrier::class, Carrier::class);
        $this->app->concord->registerModel(\Vanilo\Cart\Contracts\Cart::class, Cart::class);
        $this->app->concord->registerModel(\Vanilo\Cart\Contracts\CartItem::class, CartItem::class);
        $this->app->concord->registerModel(\Vanilo\Channel\Contracts\Channel::class, Channel::class);
        $this->app->concord->registerModel(\Konekt\Customer\Contracts\Customer::class, Customer::class);
        $this->app->concord->registerModel(\Konekt\User\Contracts\Invitation::class, Invitation::class);
        $this->app->concord->registerModel(\Vanilo\Links\Contracts\LinkGroup::class, LinkGroup::class);
        $this->app->concord->registerModel(\Vanilo\Links\Contracts\LinkGroupItem::class, LinkGroupItem::class);
        $this->app->concord->registerModel(\Vanilo\Links\Contracts\LinkType::class, LinkType::class);
        $this->app->concord->registerModel(\Vanilo\MasterProduct\Contracts\MasterProduct::class, MasterProduct::class);
        $this->app->concord->registerModel(\Vanilo\MasterProduct\Contracts\MasterProductVariant::class, MasterProductVariant::class);
        $this->app->concord->registerModel(\Vanilo\Order\Contracts\Order::class, Order::class);
        $this->app->concord->registerModel(\Vanilo\Order\Contracts\OrderItem::class, OrderItem::class);
        $this->app->concord->registerModel(\Konekt\Address\Contracts\Organization::class, Organization::class);
        $this->app->concord->registerModel(\Vanilo\Payment\Contracts\Payment::class, Payment::class);
        $this->app->concord->registerModel(\Vanilo\Payment\Contracts\PaymentHistory::class, PaymentHistory::class);
        $this->app->concord->registerModel(\Vanilo\Payment\Contracts\PaymentMethod::class, PaymentMethod::class);
        $this->app->concord->registerModel(\Konekt\Acl\Contracts\Permission::class, Permission::class);
        $this->app->concord->registerModel(\Konekt\Address\Contracts\Person::class, Person::class);
        $this->app->concord->registerModel(\Vanilo\Product\Contracts\Product::class, Product::class);
        $this->app->concord->registerModel(\Konekt\User\Contracts\Profile::class, Profile::class);
        $this->app->concord->registerModel(\Vanilo\Properties\Contracts\Property::class, Property::class);
        $this->app->concord->registerModel(\Vanilo\Properties\Contracts\PropertyValue::class, PropertyValue::class);
        $this->app->concord->registerModel(\Konekt\Acl\Contracts\Role::class, Role::class);
        $this->app->concord->registerModel(\Vanilo\Shipment\Contracts\Shipment::class, Shipment::class);
        $this->app->concord->registerModel(\Vanilo\Shipment\Contracts\ShippingMethod::class, ShippingMethod::class);
        $this->app->concord->registerModel(\Vanilo\Taxes\Contracts\TaxCategory::class, TaxCategory::class);
        $this->app->concord->registerModel(\Vanilo\Taxes\Contracts\TaxRate::class, TaxRate::class);
        $this->app->concord->registerModel(\Vanilo\Category\Contracts\Taxon::class, Taxon::class);
        $this->app->concord->registerModel(\Vanilo\Category\Contracts\Taxonomy::class, Taxonomy::class);
        $this->app->concord->registerModel(\Konekt\User\Contracts\User::class, User::class);
        $this->app->concord->registerModel(\Konekt\Address\Contracts\Zone::class, Zone::class);
        $this->app->concord->registerModel(\Konekt\Address\Contracts\ZoneMember::class, ZoneMember::class);

        Address::observe(AddressObserver::class);
        Adjustment::observe(AdjustmentObserver::class);
        Billpayer::observe(BillpayerObserver::class);
        Carrier::observe(CarrierObserver::class);
        CartItem::observe(CartItemObserver::class);
        Cart::observe(CartObserver::class);
        Channel::observe(ChannelObserver::class);
        Customer::observe(CustomerObserver::class);
        Invitation::observe(InvitationObserver::class);
        LinkGroupItem::observe(LinkGroupItemObserver::class);
        LinkGroup::observe(LinkGroupObserver::class);
        LinkType::observe(LinkTypeObserver::class);
        MasterProduct::observe(MasterProductObserver::class);
        MasterProductVariant::observe(MasterProductVariantObserver::class);
        Media::observe(MediaObserver::class);
        OrderItem::observe(OrderItemObserver::class);
        Order::observe(OrderObserver::class);
        Organization::observe(OrganizationObserver::class);
        PaymentHistory::observe(PaymentHistoryObserver::class);
        PaymentMethod::observe(PaymentMethodObserver::class);
        Payment::observe(PaymentObserver::class);
        Permission::observe(PermissionObserver::class);
        Person::observe(PersonObserver::class);
        Product::observe(ProductObserver::class);
        Profile::observe(ProfileObserver::class);
        Property::observe(PropertyObserver::class);
        PropertyValue::observe(PropertyValueObserver::class);
        Role::observe(RoleObserver::class);
        Shipment::observe(ShipmentObserver::class);
        ShippingMethod::observe(ShippingMethodObserver::class);
        TaxCategory::observe(TaxCategoryObserver::class);
        Taxon::observe(TaxonObserver::class);
        Taxonomy::observe(TaxonomyObserver::class);
        TaxRate::observe(TaxRateObserver::class);
        YnhBackup::observe(YnhBackupObserver::class);
        YnhServer::observe(YnhServerObserver::class);
        ZoneMember::observe(ZoneMemberObserver::class);
        Zone::observe(ZoneObserver::class);
        
        Asset::observe(AssetObserver::class);
        AssetTag::observe(AssetTagObserver::class);
        AssetTagHash::observe(AssetTagHashObserver::class);
        Honeypot::observe(HoneypotObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('am_api_utils', function () {
            return new ApiUtils();
        });
    }
}
