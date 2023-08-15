<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $casts = ['in_search' => 'boolean'];
    public function user()
    {
        return $this->morphOne('App\Models\User', 'userable');
    }
}
