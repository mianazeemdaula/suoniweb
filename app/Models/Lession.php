<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Lession extends Model
{
    protected $fillable = ['instrument_id', 'start_date', 'end_date', 'repeat', 'student_id', 'tutor_id', 'lession_duration'];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function videos()
    {
        return $this->hasMany(LessionVideos::class);
    }

    public function libraries()
    {
        return $this->belongsToMany('App\Models\Library','lession_libraries');
    }

    public function logs()
    {
        return $this->hasMany(LessionLogs::class);
    }

    public function notes()
    {
        return $this->hasMany(LessionNotes::class);
    }

    public function times()
    {
        return $this->hasMany(LessionTiming::class);
    }

    public function group()
    {
        return $this->hasMany(GroupUser::class,'lesson_id')->orderBy('id');
    }

    public function tutor()
    {
        return $this->belongsTo(User::class,'tutor_id');
    }
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
