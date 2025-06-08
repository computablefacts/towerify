<?php

namespace Wave\Http\Controllers\Billing\Webhooks;

use Wave\Plan;
use Wave\Subscription;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PaddleWebhook extends Controller
{
    public $paddle_url;
    public function handler(Request $request)
    {
        $event = $request->get('event_type', null);

        switch ($event) {
            case 'subscription.canceled':
                $this->subscriptionCancelled($request);
                break;
            default:
                break;
        }
    
        return response()->json(['message' => 'Webhook handled successfully'], 200);
    }

    protected function subscriptionCancelled(Request $request)
    {
        $subscriptionId = $request->input('data.id'); // Adjusted to match the payload structure

        // Ensure the subscription ID is provided
        if (is_null($subscriptionId)) {
            Log::warning('Subscription ID missing in subscriptionCancelled webhook.');
            return;
        }

        $subscription = Subscription::where('vendor_subscription_id', $subscriptionId)->where('status', 'active')->first();
        $subscription->cancel();
    }
}
