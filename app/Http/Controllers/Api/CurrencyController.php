<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;



class CurrencyController extends Controller
{
    public function index() {
        $lastRecord = Currency::latest()->first();
        $now = Carbon::now();
        if($lastRecord){
            $lastRecordDate = Carbon::parse($lastRecord->created_at);
            $diff = $now->diffInMinutes($lastRecordDate);
            if($diff > 60){
                $this->fetch();
            }
        }else{
            $this->fetch();
        }
        $data = Currency::all();
        return response()->json($data,200);
    }

    public function fetch(){
       $url =  "https://api.freecurrencyapi.com/v1/latest?apikey=fca_live_osVQILWoV3tdNL7EbNXFJOM98RSRjs8Z8CyddX2U&currencies=EUR%2CUSD%2CGBP";
       $response = Http::get($url);
       $data = $response->json();
       $currencies = $data['data'];
        foreach($currencies as $key => $value){
            $currency = Currency::where('name',$key)->first();
            if($currency){
                $currency->rate = $value;
                $currency->save();
            }else{
                $currency = new Currency();
                $currency->name = $key;
                $currency->rate = $value;
                $currency->save();
            }
        }
        return true;
    }
}
