<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonRequestDetails extends Model
{
    public function tutor()
    {
        return $this->belongsTo(User::class,'tutor_id');
    }
}
