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

    function getAllInstruments()  {
        $data = Instrument::where('status', true)->orderBy('order', 'asc')->get();
        return response()->json($data, 200);
    }

    
    public function searchByName(Request $request)
    {
        $text = $request->text;
        $user = $request->user();
        $data = [];
        $query =  User::with(['tutorRating', 'tutorToughtHours', 'tutorCountReviews', 'tutorVideos', 'tutorTimes' => function ($q) {
            $q->where('from_time', '>=', Carbon::now());
            $q->where('booked', false);
        }, 'instruments', 'userable'])->withCount(['tutorLessions as student_count' => function($query) {
            $query->select(DB::raw('count(distinct `student_id`)'));
        }])->withCount(['tutorLessions as lesson_count' => function($query) {
            $query->select(DB::raw('count(distinct `id`)'));
        }])->whereHasMorph('userable', Tutor::class, function ($a) {
            $a->where('in_search', false);
        })
        ->inRandomOrder()->where('name', 'like', "%${text}%");
        if($user){
            $ids = $user->blockedUsers()->pluck('id');
            $q->whereNotIn('id', $ids);
        }

        $data['tutors'] = $query->get();

        $data['intruments'] = Instrument::where('name', 'like', "%${text}%")->where('status', true)->get();
        return response()->json(['data' => $data]);
    }

    function teachersByInstrument($id)  {
        $user = auth()->user();
        $data = Instrument::with(['tutors' => function ($q) use($user) {
            $q->with(['tutorRating', 'tutorToughtHours', 'instruments', 'tutorCountReviews', 'tutorVideos', 'tutorTimes' => function($t){
                $t->where('booked', false);
            }, 'userable'])->whereHasMorph('userable', Tutor::class, function ($a) {
                $a->where('in_search', false);
            })->inRandomOrder();
            // $q->withCount(['tutorLessions as active_students' => function ($a) {
            //     $a->select(DB::raw('count(distinct `student_id`) as aggregate'));
            // }]);
            $q->withCount(['tutorLessions as student_count' => function($query) {
                $query->select(DB::raw('count(distinct `student_id`)'));
            }])->withCount(['tutorLessions as lesson_count' => function($query) {
                $query->select(DB::raw('count(distinct `id`)'));
            }]);
            if($user){
                $ids = $user->blockedUsers()->pluck('id');
                $q->whereNotIn('id',$ids);
            }
        }])->where('id', $id)->first();
        return response()->json($data['tutors'] ?? [], 200);
    }
}
