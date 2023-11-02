<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Fcm;
use App\Http\Controllers\Controller;
use App\Models\Lession;
use App\Models\LessonRequest;
use App\Models\LessonRequestDetails;
use App\Models\LessionTiming;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Notifications;
use App\Models\Review;
use App\Models\TutorTime;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// $date = Carbon::parse($userSuppliedDate, auth()->user()->timezone)->setTimezone('UTC');
// // When display a date from the database, convert to user timezone.
// $date = Carbon::parse($databaseSuppliedDate, 'UTC')->setTimezone($user->timezone);

class LessionRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $startDate = Carbon::parse(Carbon::now(), $user->time_zone);
        $lession = Lession::where(function ($a) use ($startDate) {
            $a->where('status', 'approved');
            $a->where('start_at', '>=', $startDate);
        })->where(function ($q) use ($user) {
            $q->where('student_id', $user->id);
            $q->orWhere('tutor_id', $user->id);
        })->with(['notes', 'libraries', 'videos', 'tutor', 'student', 'instrument'])->whereHas('tutor')->whereHas('student')->orderBy('start_at')->get();
        // join('lession_timings', 'lession_timings.lession_id', '=', 'lessions.id')->orderBy('lession_timings.end_time')->get();
        // >sortBy('times.end_time')

        return response()->json(['status' => true, 'data' => $lession], 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'instrument_id' => 'required|integer',
                'duration' => 'required|integer',
                'start' => 'required',
                'end' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['required' => $validator->errors()->first()], 200);
            } else {
                $fromDateTime = Carbon::parse($request->start, auth()->user()->time_zone)->setTimezone('UTC');
                $toDateTime = Carbon::parse($request->end, auth()->user()->time_zone)->setTimezone('UTC');
                $tutorIds = TutorTime::where('from_time', $fromDateTime)->get('user_id');
                $ids = [];
                foreach ($tutorIds as $key => $value) {
                    $ids[] = $value['user_id'];
                }
                $tutorIds = array_unique($ids);
                $tutors = User::whereIn('id', $tutorIds)->whereHas('instruments', function($q) use($request) {
                    $q->where('id', $request->instrument_id);
                })->get();
                // return response()->json(['status' => true, 'data' => $tutors], 200);
                $notifications = [];
                $lession = new LessonRequest();
                $lession->instrument_id = $request->instrument_id;
                $lession->student_id = $request->user()->id;
                $lession->lession_duration = $request->duration;
                $lession->start_at = $fromDateTime;
                $lession->end_at = $toDateTime;
                // $lession->tutor_time_id = $value['id'];
                $lession->save();
                foreach ($tutors as $tutor) {
                    $notification = new Notifications();
                    $notification->user_id = $tutor->id;
                    $notification->user_from = $request->user()->id;
                    $notification->title = 'Lesson Request';
                    $notification->body = 'Student: ' . $lession->student->name;
                    $notification->notification_time = Carbon::parse($lession->start_at, $tutor->time_zone)->setTimezone('UTC');
                    $notification->data = ['id' => $lession->id, 'type' => 'lesson_request'];
                    $notification->save();
                    $notifications[] = $notification->id;
                    Fcm::sendNotification($notification);
                }
                
                // Notifications for Student
                $notification = new Notifications();
                $notification->user_id = Auth::id();
                $notification->user_from = Auth::id();
                $notification->title = 'Teacher request';
                $notification->body = 'Finding tutor';
                $notification->notification_time = Carbon::parse($lession->start_at, $lession->student->time_zone)->setTimezone('UTC');
                $notification->data = ['id' => $lession->id, 'type' => 'lesson_request'];
                $notification->save();
                $notifications[] = $notification->id;
                DB::commit();
                Fcm::sendNotification($notification);
                return response()->json(['status' => true, 'data' => $lession], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function show($id)
    {
        $lession = LessonRequest::with(['student', 'instrument','reqDetails.tutor' => function($q){
            $q->with(['tutorRating', 'tutorToughtHours', 'tutorCountReviews', 'tutorVideos', 'tutorTimes' => function ($q) {
                $q->where('from_time', '>=', Carbon::now());
                $q->where('booked', false);
            }, 'instruments', 'userable'])->withCount(['tutorLessions as active_students' => function ($a) {
                $a->select(DB::raw('count(distinct `student_id`) as aggregate'));
            }]);
        }])->find($id);
        return response()->json(['status' => true, 'data' => $lession], 200);
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $lrequest = LessonRequest::find($id);
        if($lrequest->status == 'approved'){
            return response()->json(['message' => "Lesson already approved" ], 204);
        }
        $tutorTime = TutorTime::where('user_id', $request->tutor_id)->where('from_time', Carbon::parse($lrequest->start_at)->setTimezone('UTC'))->first();
        if($tutorTime){
            $tutorTime->booked = true;
            $tutorTime->save();
            // update request
            $lrequest->status = 'approved';
            $lrequest->save();
            // create new lesson
            $lession = new Lession();
            $lession->instrument_id = $lrequest->instrument_id;
            $lession->student_id = $lrequest->student_id;
            $lession->tutor_id = $request->tutor_id;
            $lession->lession_duration = $lrequest->lession_duration;
            $lession->start_at = Carbon::parse($lrequest->start_at)->setTimezone('UTC');
            $lession->end_at = Carbon::parse($lrequest->end_at)->setTimezone('UTC');
            $lession->tutor_time_id = $tutorTime->id;
            $lession->status = 'approved';
            $lession->fee_paid = true;
            $lession->fee = $request->fee;
            $lession->save();
            
            if($request->payment_method){
                if($request->payment_method == 'wallet'){
                    $user->updateBalance(-$request->fee, $lession->tutor_id, 'Lesson fee');
                }
            }
            // send notification
            $body = 'Stduent: ' . $lession->student->name;
            $notification = new Notifications();
            $notification->user_id = $lession->tutor_id;
            $notification->user_from = $lession->student_id;
            $notification->title = 'Lesson approved';
            $notification->body = $body;
            $notification->notification_time = Carbon::parse($lession->start_at, $lession->tutor->time_zone)->setTimezone('UTC');
            $notification->data = ['id' => $lession->id, 'type' => 'lession'];
            $notification->save();
            Fcm::sendNotification($notification);
            return $this->show($id);
        }else{
            return response()->json(['status' => false], 204);
        }
    }

    public function destroy($id)
    {
        //
    }

    public function tutorApply(Request $request)
    {
        $lession = LessonRequest::find($request->lessonId);
        $tutorFee = $request->user()->instruments()->where('instrument_id', $lession->instrument_id)->first()->pivot->fee ?? 1;
        $details = new LessonRequestDetails();
        $details->lesson_request_id = $request->lessonId;
        $details->tutor_id = $request->user()->id;
        $details->response = $request->response;
        $details->tutor_time_id = $request->tutor_time_id;
        $details->fee = $tutorFee;
        $details->save();
        return $this->show($request->lessonId);
    }

    public function updateStats(Request $request)
    {
        $details =  LessonRequest::find($request->lesson_id);
        $details->status = $request->status;
        $details->save();
        return $this->show($details->id);
    }
}
