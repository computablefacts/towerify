<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscribeRequest;
use App\Models\Customer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Konekt\Customer\Models\CustomerType;

class StripeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function plan()
    {
        return view('subscriptions.plans');
    }

    public function subscribe(SubscribeRequest $request)
    {
        $plan = trim($request->string('plan'));

        /** @var User $user */
        $user = Auth::user();
        $user->stripe_tx_id = Str::uuid()->toString();
        $user->save();

        return $request->user()
            ->newSubscription('default', $plan)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('subscribed', ['tx_id' => $user->stripe_tx_id]),
                'cancel_url' => route('home'),
            ]);
    }

    public function subscribed(string $tx_id)
    {
        /** @var User $user */
        $user = User::where('stripe_tx_id', $tx_id)->firstOrFail();

        if ($user) {

            Auth::login($user); // otherwise the tenant will not be properly set

            /** @var \Stripe\Customer $customer */
            $customer = $user->asStripeCustomer();

            if ($customer) {

                $c = Customer::create([
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'firstname' => null,
                    'lastname' => null,
                    'company_name' => $customer->name,
                    'tax_nr' => $customer->tax_ids && $customer->tax_ids->isNotEmpty() ? $customer->tax_ids->first()?->value : null,
                    'type' => $customer->tax_ids && $customer->tax_ids->isNotEmpty() ? CustomerType::ORGANIZATION : CustomerType::INDIVIDUAL,
                ]);

                if ($c) {
                    $user->customer_id = $c->id;
                    $user->stripe_tx_id = null;
                    $user->save();
                }
            }
        }
        return redirect()->route('home');
    }

    public function customerPortal(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        if (!$user->hasStripeId()) {
            $user->createAsStripeCustomer();
        }
        return $user->redirectToBillingPortal(route('home'));
    }
}
