<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Instrument;
use App\Models\Language;
use Illuminate\Support\Facades\Auth;
use App\Models\Lession;
use App\Models\AppLoginLogs;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $user = User::find($id);
        if ($user->hasRole('tutor')) {
            $user = $user->getTutor($user->id);
        }
        $data['user'] = $user;
        $data['instruments'] = Instrument::all();
        $data['languages'] = Language::all();
        return response()->json(['status' => true, 'data' => $data]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        if ($user->hasRole('tutor')) {
            $user = $user->getTutor($user->id);
        }
        $data['user'] = $user;
        $data['instruments'] = Instrument::with(['category'])->get();
        $data['languages'] = Language::all();
        return response()->json(['status' => true, 'data' => $data]);
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

    public function teachingHours()
    {
        $user = Auth::user();
        $data['hours'] = $user->tutorToughtHours;
        $isTutor = $user->userable_type == 'App\Models\Tutor';
        $data['lessions'] = Lession::join('lession_logs', 'lession_logs.lession_id', '=', 'lessions.id')
            ->join('users', $isTutor ? 'lessions.student_id' :  'lessions.tutor_id', '=', 'users.id')
            ->join('instruments','lessions.instrument_id', '=', 'instruments.id')
            ->groupBy('lessions.id', 'users.name', 'lessions.start_at')
            ->where($isTutor ? 'lessions.tutor_id' : 'lessions.student_id', $user->id)
            ->orderBy('lessions.start_at', 'desc')
            ->get(['lessions.id', 'users.name','users.image','instruments.name as inst_name', 'lessions.start_at', DB::raw('SUM(TIMESTAMPDIFF(MINUTE,start_time,end_time) / 60) as value')]);
        return response()->json(['status' => true, 'data' => $data]);
    }


    public function addAppLoginLogs(Request $request)
    {
        $auth = auth()->user();
        $fromDateTime = Carbon::parse($request->start, $auth->time_zone)->setTimezone('UTC');
        $toDateTime = Carbon::parse($request->end, $auth->time_zone)->setTimezone('UTC');

        $log = new AppLoginLogs;
        $log->user_id = $auth->id;
        $log->from_time = $fromDateTime;
        $log->to_time = $toDateTime;
        $log->save();
        return response()->json(['status' => true]);
    }

    public function blockUser(Request $request)  {
        auth()->user()->blockedUsers()->attach($request->id);
        $data = auth()->user()->blockedUsers()->pluck('id');
        return response()->json($data, 200);
    }
}
