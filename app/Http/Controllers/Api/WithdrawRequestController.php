<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\WithdrawRequest;
use App\Models\DueTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Currency;

use Stripe\Stripe;
use Stripe\Transfer;
use Stripe\Balance;

class WithdrawRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = WithdrawRequest::where('user_id', auth()->user()->id)
        ->with(['user' => function($q){
            $q->select('id','name','image');
        }])->latest()->get();
        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $auth = auth()->user();
            $account = $auth->paymentGateways()->wherePivot('payment_gateway_id', $request->payment_gateway_id)->first();
            if(!$account){
                return response()->json(['message' => 'Payment gateway not found'], 422);
            }
            $amount = $request->amount;
            $rate = Currency::whereName($account->currency)->first();
            if($rate){
                $amount = $amount * $rate->rate;
            }
            if($amount > $auth->balance || ($auth->balance - $amount) < 0){
                return response()->json(['message' => 'Insufficient balance'], 422);
            }
            $request->validate([
                'amount' => 'required|numeric|min:1|max:' . $amount,
                'payment_gateway_id' => 'required|exists:payment_gateways,id',
            ]);
            
            Stripe::setApiKey(env('STRIPE_SECRET'));
            // check balance form Stipe if avaialable
            $stripeBalance = Balance::retrieve();
            $balance = collect($stripeBalance['connect_reserved'])->where('currency', strtolower($account->currency))->first();
            $status = 'pending';
            if($balance['amount'] > ($request->amount * 100)){
                $status = 'completed';
            }
            $withdrawRequest = new WithdrawRequest();
            $withdrawRequest->user_id = $auth->id;
            $withdrawRequest->payment_gateway_id = $request->payment_gateway_id;
            $withdrawRequest->amount = -($request->amount);
            $withdrawRequest->currency = $account->currency;
            $withdrawRequest->status = $status;
            $withdrawRequest->save();
            $auth->balance -= $amount;
            $auth->save();
            if($withdrawRequest->payment_gateway_id >= 1  && $withdrawRequest->payment_gateway_id <= 3){
                if($account){
                    $destination = $account->pivot->account;
                    $transfer = Transfer::create([
                        'amount' => intval($request->amount * 100),
                        'currency' => $account->currency,
                        'destination' => $destination,
                    ]);
                    $withdrawRequest->payment_id = $transfer->id;
                    $withdrawRequest->account = $destination;
                    $withdrawRequest->save();
                    $meta = [
                        'tx_id' => $transfer->id,
                        'tx_amount' => -($request->amount),
                        'tx_currency' => $account->currency,
                    ];
                    $auth->updateBalance(-($request->amount), $auth->id, 'Withdraw done', true, $meta);
                }
            }
            DB::commit();
            return response()->json([
                'message' => 'Withdraw request created successfully',
                'data' => $withdrawRequest,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
