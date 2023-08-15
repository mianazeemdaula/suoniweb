<?php


namespace App\Helpers;
use App\Models\PaymentCard;
use Stripe\Stripe;
use Stripe\Token;
use Stripe\Charge;
use Stripe\Exception\CardException;

class StripePayment{
    
    static public function cardPayment(PaymentCard $card, $amount)
    {
        $stripe = Stripe::setApiKey(env('STRIPE_SECRET'));
        try {
            $token = Token::create([
                'card' => [
                    'number' => $card->card_no,
                    'exp_month' => $card->expiry_month,
                    'exp_year' => $card->expiry_year,
                    'cvc' => $card->cvc,
                ],
            ]);

            $charge = Charge::create([
                'card' => $token->id,
                'currency' => 'USD',
                'amount' => $amount,
                'description' => 'wallet',
            ]);
            return $charge;
        } catch (CardException $e) {
            $error = $e->getError();
            $code = $e->getHttpStatus();
            $message = $e->getMessage();
            return $error;
        }
    }

    // $payment = StripePayment::cardPayment(PaymentCard::find($request->card), intval($request->total_amount) * 100);
    //             if($payment && isset($payment['id'])){
    //                 $pay = new OrderPayment();
    //                 $pay->order_id = $order->id;
    //                 $pay->gateway = 'stripe';
    //                 $pay->payment_id = isset($payment['id']) ? $payment['id'] : "";
    //                 $pay->status = isset($payment['id']) ? 'paid' : 'declined';
    //                 $pay->data = json_encode($payment);
    //                 $pay->save();
    //             }else{
    //                 DB::rollback();
    //                 return response()->json(['message' => $payment['message']], 422);
    //             }
}