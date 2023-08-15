<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Instrument;
use Illuminate\Http\Request;
use App\Models\Tutor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function searchByName(Request $request)
    {
        $text = $request->text;
        $data = [];
        $data['tutors'] =  User::with(['tutorRating', 'tutorToughtHours', 'tutorCountReviews', 'tutorVideos', 'tutorTimes' => function ($q) {
            $q->where('from_time', '>=', Carbon::now());
            $q->where('booked', false);
        }, 'instruments', 'userable'])->withCount(['tutorLessions as active_students' => function ($a) {
            $a->select(DB::raw('count(distinct `student_id`) as aggregate'));
        }])->whereHasMorph('userable', Tutor::class, function ($a) {
            $a->where('in_search', false);
        })
        ->inRandomOrder()->where('name', 'like', "%${text}%")->get();

        $data['intruments'] = Instrument::where('name', 'like', "%${text}%")->where('status', true)->get();
        return response()->json(['data' => $data]);
    }
}
