<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorVideos extends Model
{
    //

    protected $fillable = ['url'];
    protected $hidden = [ 'created_at', 'updated_at'];
}
