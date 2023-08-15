<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['lession_id', 'rating', 'comment'];

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
