<?php

namespace App\Listeners;

use App\Enums\ProductTypeEnum;
use App\Helpers\ProductOrProductVariant;
use App\Models\Address;
use App\Models\Billpayer;
use App\Models\Customer;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\YnhOrder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Konekt\Customer\Models\CustomerType;
use Konekt\User\Models\UserType;
use Vanilo\Order\Events\OrderWasCreated;
use Vanilo\Order\Models\FulfillmentStatus;
use Vanilo\Order\Models\OrderStatus;
use Vanilo\Support\Utils\Addresses;

class OrderCreatedListener extends AbstractListener
{
    protected function handle2($event): void
    {
        if (!($event instanceof OrderWasCreated)) {
            throw new \Exception('Invalid event type!');
        }

        $order = $event->getOrder();
        $user = User::where('id', $order->user_id)->firstOrFail();

        Auth::login($user); // otherwise the tenant will not be properly set

        $this->createOrUpdateCustomerInfos($user, $order);
        $this->processOrder($order);
    }

    private function createOrUpdateCustomerInfos(User $user, Order $order): void
    {
        $billPayer = $order->getBillpayer();

        if ($billPayer->isOrganization()) {

            $customer = Customer::where('tax_nr', $billPayer->getTaxNumber())->first();

            if (isset($customer)) {
                $this->updateCustomer($billPayer, $customer);
            } else {

                $customer = Customer::where('company_name', $billPayer->getCompanyName())->first();

                if (isset($customer)) {
                    $this->updateCustomer($billPayer, $customer);
                } else {
                    $customer = $this->createCustomer($billPayer);
                }
            }
        } else {

            $customer = Customer::where('email', $billPayer->getEmail())->first();

            if (isset($customer)) {
                $this->updateCustomer($billPayer, $customer);
            } else {

                $customer = Customer::where('firstname', $billPayer->getFirstName())
                    ->where('lastname', $billPayer->getLastName())
                    ->first();

                if (isset($customer)) {
                    $this->updateCustomer($billPayer, $customer);
                } else {
                    $customer = $this->createCustomer($billPayer);
                }
            }
        }

        $customer->is_active = true;
        $customer->last_purchase_at = $order->ordered_at;
        $customer->save();

        $order->customer_id = $customer->id;
        $order->save();

        $this->updateCustomerAddresses($customer, $billPayer->getBillingAddress());
        $this->updateUserOrCreateInvitation($customer, $user);
        $this->updateBillPayerOrCreateInvitation($customer, $billPayer);
    }

    private function createCustomer(Billpayer $billPayer): Customer
    {
        return Customer::create([
            'email' => $billPayer->getEmail(),
            'phone' => $billPayer->getPhone(),
            'firstname' => $billPayer->getFirstName(),
            'lastname' => $billPayer->getLastName(),
            'company_name' => $billPayer->getCompanyName(),
            'tax_nr' => $billPayer->getTaxNumber(),
            'type' => $billPayer->isOrganization() ? CustomerType::ORGANIZATION : CustomerType::INDIVIDUAL,
        ]);
    }

    private function updateCustomer(Billpayer $billPayer, Customer $customer): void
    {
        if (!$customer->getEmail()) {
            $customer->email = $billPayer->getEmail();
        }
        if (!$customer->getPhone()) {
            $customer->phone = $billPayer->getPhone();
        }
        if (!$customer->getFirstName()) {
            $customer->firstname = $billPayer->getFirstName();
        }
        if (!$customer->getLastName()) {
            $customer->lastname = $billPayer->getLastName();
        }
        if (!$customer->getCompanyName()) {
            $customer->company_name = $billPayer->getCompanyName();
        }
        if (!$customer->getTaxNumber()) {
            $customer->tax_nr = $billPayer->getTaxNumber();
        }
    }

    private function updateCustomerAddresses(Customer $customer, Address $address1): void
    {
        $count = $customer->addresses()
            ->get()
            ->filter(function (Address $address2) use ($address1) {
                return Addresses::are($address1, $address2)->identical();
            })
            ->count();

        if ($count === 0) {
            $customer->addresses()->save($address1);
        }
    }

    private function updateUserOrCreateInvitation(Customer $customer, User $user): void
    {
        $user->is_active = true;
        $user->customer_id = $customer->id;
        $user->save();
    }

    private function updateBillPayerOrCreateInvitation(Customer $customer, Billpayer $billPayer): void
    {
        $user = User::where('email', $billPayer->getEmail())->first();
        if (isset($user)) {
            if (!isset($user->name)) {
                $user->name = $billPayer->getFullName();
            }
            $user->is_active = true;
            $user->customer_id = $customer->id;
            $user->save();
        } else {
            $invitation = Invitation::where('email', $billPayer->getEmail())->first();
            if (!$invitation) {
                $invitation = Invitation::createInvitation($billPayer->getFullName(), $billPayer->getEmail(), UserType::CLIENT(), ['customer_id' => $customer->id], 7);
            }
        }
    }

    private function processOrder(Order $order): void
    {
        $hasServerItem = false;

        foreach ($order->getItems() as $orderItem) {

            $product = ProductOrProductVariant::create($orderItem->product);

            for ($quantity = 0; $quantity < $orderItem->quantity; $quantity++) {
                if ($product->isApplication()) {
                    $this->newApplication($orderItem, $product);
                } else if ($product->isServer()) {
                    $this->newServer($orderItem, $product);
                    $hasServerItem = true;
                } else {
                    Log::error('invalid product type (order_item=' . $orderItem->id . ')');
                }
            }
        }
        if (!$hasServerItem) {

            // Here, the customer only bought applications: the order has now been fulfilled!
            $order->status = OrderStatus::COMPLETED();
            $order->save();
        }
    }

    private function newApplication(OrderItem $orderItem, ProductOrProductVariant $product): void
    {
        YnhOrder::create([
            'order_id' => $orderItem->order_id,
            'order_item_id' => $orderItem->id,
            'product_type' => ProductTypeEnum::APPLICATION,
        ]);

        // Applications items are packaged and ready to deploy!
        $orderItem->fulfillment_status = FulfillmentStatus::FULFILLED();
        $orderItem->save();
    }

    private function newServer(OrderItem $orderItem, ProductOrProductVariant $product): void
    {
        YnhOrder::create([
            'order_id' => $orderItem->order_id,
            'order_item_id' => $orderItem->id,
            'product_type' => ProductTypeEnum::SERVER,
        ]);

        // A server item is marked as fulfilled when the host is configured
        // See class ConfigureHostListener
    }
}
