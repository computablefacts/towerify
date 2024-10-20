<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;

class StripeEventListener implements ShouldQueue
{
    protected function handle2(WebhookReceived $event)
    {
        Log::debug($event->payload);
        
        if ($event->payload['type'] === 'invoice.payment_succeeded') {
            // TODO : create Customer and set the user's customer_id field
        }
    }
}
