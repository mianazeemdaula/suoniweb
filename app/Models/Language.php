<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['lang'];
    protected $hidden = ['pivot', 'created_at', 'updated_at'];
}
