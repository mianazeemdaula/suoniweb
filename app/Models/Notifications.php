<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $casts = [
        'data' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function setNotificationTimeAttribute($value)
    // {
    //     return Carbon::parse($value, auth()->user()->time_zone)->setTimezone('UTC');
    // }

    // public function setCreatedAtAttribute($value)
    // {
    //     return Carbon::parse($value, auth()->user()->time_zone)->setTimezone('UTC');
    // }

    // public function setUpdatedAtAttribute($value)
    // {
    //     return Carbon::parse($value, auth()->user()->time_zone)->setTimezone('UTC');
    // }



    public function getNotificationTimeAttribute($date)
    {
        return Carbon::parse($date, 'UTC')->setTimezone(auth()->user()->time_zone);
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date, 'UTC')->setTimezone(auth()->user()->time_zone);
    }

    // public function getCreatedAtAttribute($value)
    // {
    //     if(auth()->check())
    //     return Carbon::parse($value, 'UTC')->setTimezone(auth()->user()->time_zone);
    //     return $value;
    // }

    // public function getUpdatedAtAttribute($value)
    // {
    //     if(auth()->check())
    //     return Carbon::parse($value, 'UTC')->setTimezone(auth()->user()->time_zone);
    //     return $value;
    // }
}
