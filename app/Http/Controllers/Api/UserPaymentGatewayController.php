<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserPaymentGateway;

class UserPaymentGatewayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = auth()->user()->paymentGateways;
        return response()->json($data, 200);
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
        //
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
        $request->validate([
            'payment_id' => 'required|exists:payment_gateways,id',
            'account' => 'sometimes',
        ]);
        $is = $request->user()->paymentGateways()->wherePivot('payment_gateway_id', $request->payment_id)->first();
        if(!$is){
            $request->user()->paymentGateways()->attach($request->payment_id,[
                'active' => true,
                'account' => $request->account ?? $request->user()->email,
            ]);
        }
        // $data = UserPaymentGateway::updateOrCreate([
        //     'user_id' => $request->user()->id,
        // ],[ 
        //     'payment_gateway_id' => $request->payment_id,
        //     'account' => $request->account ?? $request->user()->email,
        // ]);
        return $this->index();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
