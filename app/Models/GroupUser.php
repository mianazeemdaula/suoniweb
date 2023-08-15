<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupUser extends Model
{
    
    protected $casts = ['allowed' => 'boolean'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function lesson()
    {
        return $this->belongsTo('App\Models\Lession', 'lesson_id');
    }
}
