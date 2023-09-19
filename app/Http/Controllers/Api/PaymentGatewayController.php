<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PaymentGateway;

class PaymentGatewayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = auth()->user()->paymentGateways()->get();
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
        $request->validate([
            'holder_name' => 'required|string',
            'account' => 'required|string',
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
        ]);
        $user = auth()->user();
        $gateway = $user->paymentGateways()->wherePivot('payment_gateway_id', $request->payment_gateway_id)->first();
        if($gateway){
            $user->paymentGateways()->updateExistingPivot($request->payment_gateway_id, [
                'holder_name' => $request->holder_name,
                'account' => $request->account,
            ]);
        }else{
            $user->paymentGateways()->attach($request->payment_gateway_id, [
                'holder_name' => $request->holder_name,
                'account' => $request->account,
            ]);
        }
        return $this->index();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $gateway = $user->paymentGateways()->wherePivot('payment_gateway_id', $request->payment_gateway_id)->first();
        return response()->json($gateway);
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
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    function activePaymentGateways() {
        
        $data = \App\Models\PaymentGateway::where('active', 1)->get();
        return response()->json($data);
    }
}
