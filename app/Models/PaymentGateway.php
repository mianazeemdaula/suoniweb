<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'active',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_payment_gateways')
        ->withPivot(['account', 'active', 'holder_name']);
    }
}
