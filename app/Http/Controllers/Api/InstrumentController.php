<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Instrument;
use App\Models\Tutor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstrumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Instrument::where('status', true)->orderBy('order', 'asc')->get();
        return response($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        // Add visited instrument to history
        $count = $user->instrumentHistory()->wherePivot('instrument_id', $id)->first();
        if (!$count)
            $user->instrumentHistory()->attach($id);
        else
            $count->update(['pivot_updated_at', Carbon::now()]);

        // Get tutors information
        $userIds = Instrument::with(['tutors' => function ($q) {
            $q->with(['tutorRating', 'tutorToughtHours', 'instruments', 'tutorCountReviews', 'tutorVideos', 'tutorTimes', 'userable'])->whereHasMorph('userable', Tutor::class, function ($a) {
                $a->where('in_search', false);
            })->inRandomOrder();
            $q->withCount(['tutorLessions as active_students' => function ($a) {
                $a->select(DB::raw('count(distinct `student_id`) as aggregate'));
            }]);
        }])->where('id', $id)->first();

        return $userIds;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getLatest()
    {
        $data['instruments'] = Instrument::where('status', true)->orderBy('id', 'desc')->get();
        $data['history'] = Auth::user()->instrumentHistory()->orderBy('pivot_updated_at', 'desc')->get();
        $data['favorites'] = Auth::user()->instrumentFavorite;

        return response()->json(['status' => true, 'data' => $data]);
    }
}
