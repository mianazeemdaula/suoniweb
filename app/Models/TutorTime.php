<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TutorTime extends Model
{
    //

    public function getFromTimeAttribute($date)
    {
        if(!auth()->check()) return $date;
        return Carbon::parse($date, 'UTC')->setTimezone(auth()->user()->time_zone);
    }

    public function getToTimeAttribute($date)
    {
        if(!auth()->check()) return $date;
        return Carbon::parse($date, 'UTC')->setTimezone(auth()->user()->time_zone);
    }
}
