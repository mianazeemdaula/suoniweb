<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Stripe
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentHooksController extends Controller
{
    function stripePayment(Request $request) {
        if($request->id) {
            Log::debug($request->data);
        }
        // switch ($event->type) {
        //     case 'payment_intent.amount_capturable_updated':
        //       $paymentIntent = $event->data->object;
        //     case 'payment_intent.canceled':
        //       $paymentIntent = $event->data->object;
        //     case 'payment_intent.created':
        //       $paymentIntent = $event->data->object;
        //     case 'payment_intent.partially_funded':
        //       $paymentIntent = $event->data->object;
        //     case 'payment_intent.payment_failed':
        //       $paymentIntent = $event->data->object;
        //     case 'payment_intent.processing':
        //       $paymentIntent = $event->data->object;
        //     case 'payment_intent.requires_action':
        //       $paymentIntent = $event->data->object;
        //     case 'payment_intent.succeeded':
        //       $paymentIntent = $event->data->object;
        //     // ... handle other event types
        //     default:
        //       echo 'Received unknown event type ' . $event->type;
        //   }
        
        // Log::debug($request->all());
    }
}
