<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasApiTokens, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'clock_24', 'time_zone'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'pivot'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $with = ['userable'];



    public function getImageAttribute($value)
    {
        $avatar = '';
        if ($value == null) {
            $avatar = asset('images/avatar.jpg');
        } else if (strpos($value, 'http') !== false) {
            $avatar = $value;
        } else {
            $avatar = asset($value);
        }
        return $avatar;
    }

    public function getTutor($id)
    {
        return $this->where('id', $id)->with(['instruments', 'languages', 'tutorVideos', 'tutorRating', 'tutorCountReviews', 'tutorToughtHours', 'tutorTimes'])->first();
    }


    public function userable()
    {
        return $this->morphTo();
    }

    public function favouriteTutors() : BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'favourite_tutor_users', 'student_id', 'tutor_id');
    }

    public function instruments(): BelongsToMany
    {
        return $this->belongsToMany(Instrument::class, null, 'tutor_id')
        ->withPivot(['fee','group_fee'])
        ->where('status', true);
    }

    // public function instrumentCats()
    // {
    //     return $this->belongsToMany('App\Models\InstrumentCategory',null,'tutor_id');
    // }

    public function tutorVideos()
    {
        return $this->hasMany(TutorVideos::class, 'tutor_id', 'id');
    }

    public function appLoginLogs()
    {
        return $this->hasMany(AppLoginLogs::class, 'user_id', 'id');
    }

    public function appLoginTime()
    {
        return $this->appLoginLogs()->selectRaw("SUM(TIMESTAMPDIFF(MINUTE,from_time,to_time)) as value")->groupBy('user_id');
    }

    public function languages()
    {
        return $this->belongsToMany('App\Models\Language', null, 'tutor_id');
    }

    public function tutorLessions()
    {
        return $this->hasMany('App\Models\Lession', 'tutor_id');
    }

    public function activeStudents()
    {
        return $this->tutorLessions()->select(DB::raw('count(distinct `student_id`) as aggregate'));
    }

    public function tutorRating()
    {
        return $this->hasOneThrough(Review::class, Lession::class, 'tutor_id')->selectRaw('avg(rating) as value')->groupBy('tutor_id');
    }

    public function tutorCountReviews()
    {
        return $this->hasOneThrough(Review::class, Lession::class, 'tutor_id')->selectRaw('count(lession_id) as value')->groupBy('tutor_id');
    }

    public function tutorToughtHours()
    {
        return $this->hasOneThrough(LessionLogs::class, Lession::class, 'tutor_id')->selectRaw("SUM(TIMESTAMPDIFF(MINUTE,start_time,end_time) / 60) as value")->groupBy('tutor_id');
    }

    public function libraries()
    {
        return $this->hasMany('App\Models\Library', 'student_id');
    }

    //
    public function studentReportTutor()
    {
        return $this->hasMany('App\Models\ReportTutor', 'student_id');
    }

    public function tutorReportStudent()
    {
        return $this->hasMany('App\Models\ReportTutor', 'tutor_id');
    }

    public function instrumentHistory()
    {
        return $this->belongsToMany('App\Models\Instrument', 'user_instrument_history', 'user_id', 'instrument_id')->withTimestamps();
    }

    public function instrumentFavorite()
    {
        return $this->belongsToMany('App\Models\Instrument', 'user_instrument_favorite', 'user_id', 'instrument_id')->withTimestamps();
    }

    public function tutorTimes()
    {
        return $this->hasMany('App\Models\TutorTime');
    }

    public function scopeWithAndWhereHas($query, $relation, $constraint)
    {
        return $query->whereHas($relation, $constraint)
            ->with([$relation => $constraint]);
    }
}
