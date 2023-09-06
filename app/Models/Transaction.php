<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $casts = [
        'user_id' => 'integer',
        'user_from' => 'integer',
    ];

    protected $fillable = [
        'amount',
        'type',
        'description',
        'user_id',
        'user_from',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_from');
    }
}
