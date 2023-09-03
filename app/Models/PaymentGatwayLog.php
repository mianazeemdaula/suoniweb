<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGatwayLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'gatway_name',
        'response',
        'data',
        'status',
    ];

    protected $casts = [
        'response' => 'json',
        'data' => 'json',
    ];
}
