<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WithdrawRequest;
use App\Models\PaymentGateway;
use Stripe\Stripe;
use Stripe\Transfer;

class StripePayoutStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:stripe-payout-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of Stripe payouts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rows = WithdrawRequest::where('status', 'pending')
        ->whereIn('payment_gateway_id',[1,2,3])->get();
        
        Stripe::setApiKey(env('STRIPE_SECRET'));
        // process each row and update the status
        foreach ($rows as $row) {
            $payout = Transfer::retrieve($row->payment_id);
            // if ($payout->status == 'paid') {
            //     $row->update(['status' => 'completed']);
            // } elseif ($payout->status == 'failed') {
            //     $row->update(['status' => 'failed']);
            // }
        }
    }
}
