<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_gateway_id',
        'user_id',
        'account',
    ];
}
