<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Helpers\Fcm;
use App\Models\PaymentGatwayLog;
use App\Models\User;
use App\Models\Currency;
use App\Models\Notifications;
use Carbon\Carbon;

// Stripe
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Event;

class PaymentHooksController extends Controller
{
    public  function stripePayment(Request $request) {
      Log::debug($request->headers->all());
      Log::debug($request->all());
      $payload = file_get_contents('php://input');
      Stripe::setApiKey(env('STRIPE_SECRET'));
      $event = Event::constructFrom(
          json_decode($payload, true)
      );
      Log::debug($event);
        if($event->id) {
            // Log::debug($event->data);
            PaymentGatwayLog::create([
                'gatway_name' => 'stripe',
                'response' => $event->all(),
                'data' => $event->data['object']['metadata'],
                'status' => $event->type,
            ]);
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
                $lessonIds = [];
                // foreach(json_decode($metadata['lessons']) as $l) {
                //     $lesson = \App\Models\Lession::find($l);
                //     $lesson->fee_paid = true;
                //     $lesson->save();
                //     $lessonIds[] = $lesson->id;
                // }
                // foreach(json_decode($metadata['group_lessons']) as $gl) {
                //     $group_lesson = \App\Models\GroupUser::find($gl);
                //     $group_lesson->fee_paid = true;
                //     $group_lesson->save();
                //     $lessonIds[] = $gl->lesson->id;
                // }

                // foreach ($lessonIds as $id) {
                //   $noti = Notifications::whereJsonContains('data->id', $id)
                //   ->whereJsonContains('data->type','lession')
                //   ->where('queued',true)->first();
                //   if($noti){
                //     Fcm::sendNotification($noti);
                //     $noti->queued = false;
                //     $noti->save();
                //   }
                // }
              }else if($metadata['type'] == 'topup') {
                $userId = $metadata['user_id'];
                $amount = $event->data['object']['amount'];
                $currency = strtoupper($event->data['object']['currency'] ?? 'usd');
                if($currency!= 'USD'){
                  $rate = Currency::whereName($currency)->first();
                  if($rate){
                    $amount = $amount * $rate->rate;
                  }
                }
                $user = User::find($userId);
                $amount = ($amount / 100);
                $last = $user->transactions()->where('tx_id', $event->data['object']['id'])->first();
                if(!$last){
                  $meta = [
                    'tx_id' => $event->data['object']['id'],
                    'tx_amount' => ($event->data['object']['amount'] / 100),
                    'tx_currency' => $currency,
                  ];
                  User::find($userId)->updateBalance($amount, $userId, 'Topup', true, $meta);
                }
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

    public function createStripeConnectAccount()  {
      $stripe = new \Stripe\StripeClient(
        env('STRIPE_SECRET')
      );
      $account = $stripe->accounts->create([
        'type' => 'express',
      ]);
      return response()->json($account);
    }
}
