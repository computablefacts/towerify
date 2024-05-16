<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Billpayer;
use App\Models\TaxRate;
use Illuminate\Support\Facades\Auth;
use Konekt\Address\Models\CountryProxy;
use Vanilo\Cart\Contracts\CartManager;
use Vanilo\Checkout\Contracts\Checkout;
use Vanilo\Foundation\Models\Cart;
use Vanilo\Foundation\Models\Order;
use Vanilo\Order\Contracts\OrderFactory;
use Vanilo\Payment\Factories\PaymentFactory;
use Vanilo\Payment\Models\PaymentHistory;
use Vanilo\Payment\Models\PaymentMethod;

class CheckoutController extends Controller
{
    /** @var Checkout */
    private $checkout;

    /** @var Cart */
    private $cart;

    public function __construct(Checkout $checkout, CartManager $cart)
    {
        $this->checkout = $checkout;
        $this->cart = $cart;
        $this->middleware('auth');
    }

    public function show()
    {
        $checkout = false;
        $user = Auth::user();

        if ($this->cart->isNotEmpty()) {
            $checkout = $this->checkout;
            if ($old = old()) {
                $checkout->update($old);
            }

            $checkout->setCart($this->cart);
        }
        if ($user) {

            $lastOrder = Order::select('orders.*')
                ->join('users', 'users.id', '=', 'orders.created_by')
                ->where('users.tenant_id', $user->tenant_id)
                ->orderBy('orders.created_at', 'desc')
                ->first();

            if ($lastOrder) {
                if ($lastOrder->billpayer) {
                    $checkout->setBillpayer($lastOrder->billpayer);
                }
                if ($lastOrder->shippingAddress) {
                    $checkout->setShippingAddress($lastOrder->shippingAddress);
                }
                if ($lastOrder->notes) {
                    $checkout->setCustomAttribute('notes', $lastOrder->notes);
                }
            }
        }

        // Apply VAT on EU customers
        $euVat = null;
        $billpayer = $checkout->getBillpayer();

        if ($billpayer && $billpayer->isEuRegistered()) {
            $euVat = TaxRate::setOrUpdateEuVat($checkout->getCart());
        }
        return view('checkout.show', [
            'checkout' => $checkout,
            'countries' => CountryProxy::all(),
            'paymentMethods' => PaymentMethod::actives()->get(),
            'euVat' => $euVat,
        ]);
    }

    public function submit(CheckoutRequest $request, OrderFactory $orderFactory)
    {
        /** @var Checkout $checkout */
        $checkout = $this->checkout;
        $checkout->update($request->all());
        $checkout->setCustomAttribute('notes', $request->get('notes'));
        $checkout->setCart($this->cart);

        /** @var Billpayer $billpayer */
        $billpayer = $checkout->getBillpayer();
        $billpayer->is_eu_registered = $billpayer->getBillingAddress()->isInEu();

        /** @var Order $order */
        $order = $orderFactory->createFromCheckout($checkout);
        $order->notes = $request->get('notes');
        $order->save();
        $this->cart->destroy();

        $paymentMethod = $request->paymentMethod();
        $payment = PaymentFactory::createFromPayable($order, $paymentMethod);
        PaymentHistory::begin($payment);
        $paymentRequest = $paymentMethod
            ->getGateway()
            ->createPaymentRequest($payment, options: ['webhookUrl' => route('payment.mollie.webhook'), 'redirectUrl' => route('payment.mollie.return', $payment->hash)]);

        // @todo the method exists check can be removed after v4 upgrade
        if (method_exists($paymentRequest, 'getRemoteId') && $paymentRequest->getRemoteId()) {
            $payment->update([
                'remote_id' => $paymentRequest->getRemoteId(),
            ]);
        }

        return view('checkout.thankyou', [
            'order' => $order,
            'paymentRequest' => $paymentRequest,
        ]);
    }
}
