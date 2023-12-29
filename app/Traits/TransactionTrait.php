<?php

namespace App\Traits;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

trait TransactionTrait
{

    public function updateBalance(float $amount, int $from, string $description = null, $balanceUpdate = true )
    {
        // Create a new transaction
        $transaction = new Transaction([
            'amount' => $amount,
            'type' => $amount > 0 ? 'income' : 'withdrawal',
            'description' => $description,
            'user_id' => $this->id,
            'user_from' => $from,
        ]);

        // Associate the transaction with the user
        $this->transactions()->save($transaction);

        if($balanceUpdate){
            // Update the user's balance
            if($amount > 0){
                $this->balance += $amount;
            }else{
                $this->balance -= abs($amount);
            }
        }
        $this->save();
    }
}
