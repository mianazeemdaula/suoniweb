<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\WithdrawRequest;
use App\Models\DueTransaction;
use Illuminate\Support\Facades\Log;

use Stripe\Stripe;
use Stripe\Transfer;

class WithdrawRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = WithdrawRequest::where('user_id', auth()->user()->id)->latest()->get();
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
        $auth = auth()->user();
        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $auth->balance,
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
        ]);
        $withdrawRequest = new WithdrawRequest();
        $withdrawRequest->user_id = $auth->id;
        $withdrawRequest->payment_gateway_id = $request->payment_gateway_id;
        $withdrawRequest->amount = -($request->amount);
        $withdrawRequest->save();
        $auth->balance -= $request->amount;
        $auth->save();
        $due =  DueTransaction::create([
            'user_id' => $auth->id,
            'user_from' => $auth->id,
            'amount' => -($request->amount),
            'description' => 'Withdraw request created',
            'due_date' => now()->addDays(3),
        ]);
        if($withdrawRequest->payment_gateway_id >= 1  && $withdrawRequest->payment_gateway_id <= 3){
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $transfer = Transfer::create([
                'amount' => $request->amount / 100,
                'currency' => 'usd',
                'destination' => $auth->paymentGateways()->wherePivot('payment_gateway_id', $request->payment_gateway_id)->first()->account,
            ]);
            Log::info($transfer);
        }
        // $auth->updateBalance(-($request->amount), $auth->id, 'Withdraw request created');
        return response()->json([
            'message' => 'Withdraw request created successfully',
            'data' => $withdrawRequest,
        ]);
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
