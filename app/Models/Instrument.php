<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instrument extends Model
{   protected $fillable = ['logo', 'name', 'status'];
    protected $hidden = ['created_at', 'updated_at'];


    public function getLogoAttribute($value)
    {
       return ($value == null) ? "https://via.placeholder.com/150" : asset($value);
    }

    public function tutors()
    {
        return $this->belongsToMany(User::class, null, null, 'tutor_id')
        ->withPivot(['fee','group_fee']);
    }

    public function category()
    {
        return $this->hasMany(InstrumentCategory::class);
    }

    public function userHistory()
    {
        return $this->belongsToMany('App\Models\User','user_instrument_history','instrument_id','user_id')->withTimestamps();
    }

    public function userFavorite()
    {
        return $this->belongsToMany('App\Models\User','user_instrument_favorite','instrument_id','user_id')->withTimestamps();
    }
}
