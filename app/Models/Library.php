<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Library extends Model
{
    //

    public function getDocAttribute($value)
    {
        return asset($value);
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User','student_id');
    }
}
