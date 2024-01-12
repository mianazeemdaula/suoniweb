<?php


namespace App\Helpers;
use App\Models\PaymentCard;
use Stripe\Stripe;
use Stripe\Token;
use Stripe\Charge;
use Stripe\PaymentIntent;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Exception\CardException;
use Stripe\Transfer;
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
                'amount' => $request->amount * 100,
                'currency' => $request->currency ?? 'usd',
                'payment_method_types' => ['card'],
                'description' => $request->description ?? 'payment for lessons',
                'metadata' => ($request->data ?? []),
            ]);
            return response()->json($paymentIntent, 200);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()],422);
        }
    }

    static public function PaymentIntent($amount, Array $data = [], $currency = 'usd') {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            return PaymentIntent::create([
                'amount' => $amount * 100,
                'currency' => $currency,
                'payment_method_types' => ['card'],
                'description' => 'Payment for lessons',
                'metadata' => $data,
            ]);
        
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return ['message' => $e->getMessage()];
        }
    }

    static public function createStripeConnectAccount(Request $request)  {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $account = Account::create([
                'type' => 'express',
            ]);
            $user = $request->user();
            $gateway = $user->paymentGateways()->wherePivot('payment_gateway_id', $request->id)->first();
            if(!$gateway){
                $user->paymentGateways()->attach($request->id,[
                    'active' => false,
                    'account' => $account->id,
                    'holder_name' => $user->name ?? $user->email,
                ]);
            }else{
                $user->paymentGateways()->updateExistingPivot($request->id,[
                    'active' => false,
                    'account' => $account->id,
                ]);
            }
            // create account links
            $accountLink = AccountLink::create([
                'account' => $account->id,
                'refresh_url' => url("/api/stripe-connect-account-refresh/$request->id/$user->id"),
                'return_url' => url("/api/stripe-connect-account-return/$account->id/$user->id"),
                'type' => 'account_onboarding',
            ]);
            return response()->json($accountLink);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    static public function topupAccount($currency = 'usd') {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $topup = \Stripe\Topup::create([
                'amount' => intval(100 * 100),
                'currency' => $currency,
                'description' => 'Topup for account',
                'statement_descriptor' => 'Topup',
                'source' => 'tok_bypassPending',
            ]);
            return response()->json($topup);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 422);
        }
    }
}