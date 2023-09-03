<?php


namespace App\Helpers;
use App\Models\PaymentCard;
use Stripe\Stripe;
use Stripe\Token;
use Stripe\Charge;
use Stripe\PaymentIntent;
use Stripe\Exception\CardException;
use Illuminate\Http\Request;

class StripePayment{
    
    static public function cardPayment($card,$month,$year, $cvc, $amount)
    {
        $stripe = Stripe::setApiKey(env('STRIPE_SECRET'));
        try {
            $token = Token::create([
                'card' => [
                    'number' => $card,
                    'exp_month' => $month,
                    'exp_year' => $year,
                    'cvc' => $cvc,
                ],
            ]);

            return $token;

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

    static public function getPaymentIntentClientSecret(Request $request) {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount,
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'description' => 'Payment for lessons',
                'metadata' => [
                    'lessons' => [2,65,98,25],
                    'group_lessons' => [25,6,89],
                    'type' => 'lessons',
                  ],
            ]);
            return response()->json($paymentIntent, 200);
        
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()],422);
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