<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\PaymentGatwayLog;
use App\Models\User;

// Stripe
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentHooksController extends Controller
{
    function stripePayment(Request $event) {
        if($event->id) {
            // Log::debug($event->data);
            PaymentGatwayLog::create([
                'gatway_name' => 'stripe',
                'response' => $event->all(),
                'data' => $event->data['object']['metadata'],
                'status' => $event->type,
            ]);
            $log = PaymentGatwayLog::whereJsonContains('response->id', $event->id)
            ->where('status', $event->type)
            ->first();
            switch ($event->type) {
            case 'payment_intent.amount_capturable_updated':
              $paymentIntent = $event->data['object'];
            case 'payment_intent.canceled':
              $paymentIntent = $event->data['object'];
            case 'payment_intent.created':
              $paymentIntent = $event->data['object'];
            case 'payment_intent.partially_funded':
              $paymentIntent = $event->data['object'];
            case 'payment_intent.payment_failed':
              $paymentIntent = $event->data['object'];
            case 'payment_intent.processing':
              $paymentIntent = $event->data;
            case 'payment_intent.requires_action':
              $paymentIntent = $event->data['object'];
            case 'payment_intent.succeeded':
              $metadata = $event->data['object']['metadata'];
              if($metadata['type'] == 'lessons') {
                foreach(json_decode($metadata['lessons']) as $lesson) {
                    $lesson = \App\Models\Lession::find($lesson);
                    $lesson->fee_paid = true;
                    $lesson->save();
                }
                foreach(json_decode($metadata['group_lessons']) as $group_lesson) {
                    $group_lesson = \App\Models\GroupUser::find($group_lesson);
                    $group_lesson->fee_paid = true;
                    $group_lesson->save();
                }
              }
              if($metadata['type'] == 'topup' && $log != null) {
                $userId = $metadata['user_id'];
                $amount = $event->data['object']['amount'];
                User::find($userId)->updateBalance(($amount / 100), $userId, 'Topup');
              }
            default:
              echo 'Received unknown event type ' . $event->type;
          }
        }
        
        
        // Log::debug($request->all());
    }


    function wappiAppHooks(Request $request) {
      Log::debug($request->all()); 
    }
}
