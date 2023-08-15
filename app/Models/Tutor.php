<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{

    // protected $appends = ['rating'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $casts = ['clock_24' => 'boolean', 'in_search' => 'boolean'];

    public function user()
    {
        return $this->morphOne('App\Models\User', 'userable');
    }

    public function instruments()
    {
        return $this->belongsToMany('App\Models\Instrument')->where('status', true);
    }

    public function lessions()
    {
        return $this->hasMany(Lession::class);
    }
}
