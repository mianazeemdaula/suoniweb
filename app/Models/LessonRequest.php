<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class LessonRequest extends Model
{
    public function student()
    {
        return $this->belongsTo(User::class,'student_id');
    }
    public function category()
    {
        return $this->belongsTo(InstrumentCategory::class, 'instrument_category_id');
    }

    public function instrument()
    {
        return $this->belongsTo(Instrument::class);
    }

    public function reqDetails()
    {
        return $this->hasMany(LessonRequestDetails::class);
    }

    public function getStartAtAttribute($value)
    {
        if(auth()->check())
            return Carbon::parse($value, 'UTC')->setTimezone(auth()->user()->time_zone);
        return $value;
    }

    public function getEndAtAttribute($value)
    {
        if(auth()->check())
            return Carbon::parse($value, 'UTC')->setTimezone(auth()->user()->time_zone);
        return $value;
    }
}
