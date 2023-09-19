<?php

namespace App\Traits;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

trait TransactionTrait
{
    /**
     * Update the user's balance and record a transaction.
     *
     * @param float $amount
     * @param string $type
     * @param string|null $description
     * @return void
     * @throws \Exception
     */
    public function updateBalance(float $amount, int $from, string $description = null)
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

        // Update the user's balance
        if($amount > 0){
            $this->balance += $amount;
        }else{
            $this->balance -= $amount;
        }
        $this->save();
    }
}
