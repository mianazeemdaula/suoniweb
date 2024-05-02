<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Fcm;
use App\Http\Controllers\Controller;
use App\Models\Lession;
use App\Models\LessionNotes;
use App\Models\LessionTiming;
use App\Models\LessionLogs;
use App\Models\LessionVideos;
use App\Models\GroupUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Notifications;
use App\Models\Review;
use App\Models\TutorTime;
use App\Models\Currency;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// $date = Carbon::parse($userSuppliedDate, auth()->user()->timezone)->setTimezone('UTC');
// // When display a date from the database, convert to user timezone.
// $date = Carbon::parse($databaseSuppliedDate, 'UTC')->setTimezone($user->timezone);

class LessionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $startDate = Carbon::parse(Carbon::now(), $user->time_zone);
        $lession = Lession::where(function ($a) use ($startDate) {
            $a->where('status', 'approved');
            $a->where('start_at', '>=', $startDate);
        })->where(function($q) use($user){
            $q->where('student_id', $user->id);
            $q->orWhere('tutor_id', $user->id);
            $q->orWhereHas('group', function($gr) use($user) {
                $gr->where('user_id', $user->id);
                $gr->where('allowed',true);
            });
        })->with(['notes', 'libraries', 'videos', 'tutor', 'student', 'instrument','group.user'])
        ->whereHas('tutor')->whereHas('student')->orderBy('start_at')->get();
        $lession = $lession->map(function ($item) use($user) {
            $groupMember = $item->group()->where('user_id', $user->id)->where('allowed',true)->first();
            if($item->instrument == null && $item->tutor_id != $user->id &&  $groupMember == null){
                unset($item);
                return null;
            }else{
                return $item;
            }
        })->all();
        $lession =  collect($lession)->filter();
        return response()->json(['status' => true, 'data' => $lession], 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'tutor_id' => 'required|integer',
                'duration' => 'required|integer',
                'times' => 'required',
                'fee' => 'required',
                'instrument_id' => 'required',
                'payment_type' => 'required',
            ]);
            $totalPayable = $request->fee * count($request->times);
            if($request->payment_type == 'wallet'){
                $user = $request->user();
                $totalPayable = (Currency::whereName($user->currency)->first()->rate ?? 1) * $totalPayable;
                if($user->balance < $totalPayable){
                    return response()->json(['status' => false, 'message' => 'Insufficient balance'], 422);
                }
            }
            $currency = $request->user()->currency ?? 'USD';
            $group = $request->instrument_id == 21;
            $totalAmount = 0;
            $notifications = [];
            $lessions = [];
            $lessonIds = [];
            $groupIds = [];
            $gIds = [];
            foreach ($request->times as $key => $value) {
                $startDate = Carbon::parse($value['start'], auth()->user()->time_zone)->setTimezone('UTC');
                $endDate = Carbon::parse($value['end'], auth()->user()->time_zone)->setTimezone('UTC');
                $times[] = $value;
                $lession = $newLesson = Lession::where('tutor_id',$request->tutor_id)
                ->whereNotIn('status', ['canceled','finished','reviewed'])
                ->where('tutor_time_id',$value['id'])
                ->orderBy('id','desc')->first();
                if(!$lession){
                    $lession = new Lession();
                    $lession->instrument_id = $request->instrument_id;
                    $lession->student_id = $request->user()->id;
                    $lession->tutor_id = $request->tutor_id;
                    $lession->lession_duration = $value['duration'];
                    $lession->start_at = $startDate;
                    $lession->end_at = $endDate;
                    $lession->fee = $request->fee;
                    $lession->fee_paid = true;
                    $lession->tutor_time_id = $value['id'];
                    $lession->status = 'approved';
                    $lession->currency = $currency;
                    $lession->save();
                    $totalAmount += $lession->fee;
                    $lessonIds[] = $lession->id;
                }else{
                    $lession->status = 'approved';
                    $lession->save();
                    $lessonIds[] = $lession->id;
                    $totalAmount += $lession->fee;
                }
                if($group){
                    // find the last user if lesson already canceld and same user request it again
                    $gr =  GroupUser::where('lesson_id',$lession->id)->where('user_id',$request->user()->id)->first();
                    if(!$gr){
                        $user = new GroupUser;
                        $user->user_id = $request->user()->id;
                        $user->lesson_id = $lession->id;
                        $user->allowed = true;
                        $user->fee = $request->fee;
                        $user->fee_paid =true;
                        $user->currency = $currency;
                        $user->save();
                        $totalAmount += $user->fee;
                        $groupIds[] = $user->id;
                    }else{
                        $totalAmount += $user->fee;
                        $groupIds[] = $user->id;
                        $gr->allowed = false;
                        $gr->save();
                    }
                }
                $lessions[] = $lession;
                $notification = new Notifications(); 
                $notification->user_id = $lession->tutor_id;    
                $notification->user_from = $request->user()->id;    
                $notification->title = 'Lesson approved';
                $notification->body = $group ? "Group Lesson" :  'Student: ' . $request->user()->name;
                $notification->notification_time = Carbon::parse($lession->start_at, $lession->tutor->time_zone)->setTimezone('UTC');
                $notification->data = ['id' => $lession->id, 'type' => 'lession', 'status' => 'approved'];
                $notification->save();
                $notifications[] = $notification->id;

                // Notifications for Student
                $notification = new Notifications();
                $notification->user_id = $request->user()->id;
                $notification->user_from = $lession->tutor_id;
                $notification->title = 'Lesson approved';
                $notification->body = $group ? "Group Lesson" : 'Tutor: ' . $lession->tutor->name;
                $notification->notification_time = Carbon::parse($lession->start_at, $lession->student->time_zone)->setTimezone('UTC');
                $notification->data = ['id' => $lession->id, 'type' => 'lession', 'status' => 'approved'];
                $notification->save();
                $notifications[] = $notification->id;

                // Book the time slot for tutor if its not a group lesson
                $time = TutorTime::find($lession->tutor_time_id);
                if ($time && !$time->is_group) {
                    $time->booked = true;
                    $time->save();
                }
            }
            $user = $request->user();
            $transAmount = $totalAmount;
            $rate = Currency::whereName($currency)->first();
            if($rate){
                $transAmount = $transAmount * $rate->rate;
            }
            $meta = [
                'tx_amount' => -$transAmount,
                'tx_currency' => $currency,
            ];
            if($request->payment_type == 'wallet'){
                $user->updateBalance(-$totalAmount, $request->tutor_id, 'Paid with balance', true, $meta);
            }else{
                $user->updateBalance(-$totalAmount, $request->tutor_id, 'Paid with card', false, $meta);
            }
            DB::commit();
            $notifications = Notifications::whereIn('id', $notifications)->where('queued',false)->get();
            foreach ($notifications as $value) {
                Fcm::sendNotification($value);
            }
            return response()->json(['status' => true, 'data' => $lessions[0]], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function show($id)
    {
        $lession = Lession::with(['notes', 'libraries', 'videos', 'tutor', 'times', 'student', 'instrument','group.user'])->find($id);
        return response()->json(['status' => true, 'data' => $lession], 200);
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required',
        ]);
        $lession = Lession::find($id);
        $lastStatus =  $lession->status;
        $lession->status = $request->status;
        $lession->save();
        $user = $request->user();
        $sendToId = 1;
        $body = '';
        $group = $lession->instrument_id == 21;
        $groupUser = null;
        if ($request->status == 'finished' || $request->status == 'canceled') {
            $time = TutorTime::find($lession->tutor_time_id);
            if ($time) {
                $time->booked = false;
                $time->save();
            }
            // If it is a groups lesson and student want it to canceled
            if($request->status == 'canceled' && $lession->tutor_id != $user->id && $lession->instrument_id  == 21){
                $group = GroupUser::where('lesson_id',$lession->id)->where('user_id',$user->id)->first();
                if($group){
                    $payFee = $group->fee;
                    $rate = Currency::whereName($group->currency)->first();
                    if($rate){
                        $payFee = $payFee / $rate->rate;
                    }
                    $metadata = [
                        'tx_amount' => $payFee,
                        'tx_currency' => $group->currency,
                    ];
                    $user->updateBalance($payFee, $group->user_id, 'Refunded', true, $metadata);
                    $group->delete();
                }
                $lession->status = $lastStatus;
                $lession->save();
            }
            // if request is canceled by tutor 
            // return the balance to student
            // if($request->status == 'canceled' && $lession->tutor_id == $user->id){
            if($request->status == 'canceled'){
                $payFee = $lession->fee;
                $rate = Currency::whereName($lession->currency)->first();
                if($rate){
                    $payFee = $payFee / $rate->rate;
                }
                $metadata = [
                    'tx_amount' => $payFee,
                    'tx_currency' => $lession->currency,
                ];
                $lession->student->updateBalance($payFee, $user->id, 'Refunded', true, $metadata);
            }

            // if lesson is finished by tutor
            // pay the balance to tutor

            if($request->status == 'finished'){
                if($lession->instrument_id  == 21){
                    $groups = GroupUser::where('lesson_id',$lession->id)->where('allowed',true)->get();
                    foreach ($groups as $g) {
                        $payFee = $g->fee ;
                        $rate = Currency::whereName($g->currency)->first();
                        if($rate){
                            $payFee = $payFee * $rate->rate;
                        }
                        $payFee = $payFee * 0.8;
                        $metadata = [
                            'tx_amount' => $payFee,
                            'tx_currency' => $g->currency,
                        ];
                        $lession->tutor->updateBalance($payFee, $g->user_id, 'Paid', true, $metadata);
                    }
                }else{
                    $payFee = $lession->fee;
                    $rate = Currency::whereName($lession->currency)->first();
                    if($rate){
                        $payFee = $payFee * $rate->rate;
                    }
                    $payFee = $payFee * 0.8;
                    $studentId = $lession->student_id;
                    $metadata = [
                        'tx_amount' => $payFee,
                        'tx_currency' => $lession->currency,
                    ];
                    $lession->tutor->updateBalance($payFee, $studentId, 'Paid', true, $metadata);
                }
            }
        }
        if ($request->status == 'approved' || $request->status == 'canceled' || $request->status == 'finished') {
            // update the tutor avaialble time
            
            // Delete all the request notifications for this lesson
            if($request->status == 'approved' || $request->status == 'canceled'){
                Notifications::whereJsonContains('data->id', $lession->id)
                ->whereJsonContains('data->type','lession')
                ->delete();
            }
            if ($request->status == 'approved') {
                // update the tutor avaialble time
                $time = Carbon::parse($lession->start_at, 'UTC')->setTimezone($lession->tutor->time_zone);
                $time = TutorTime::find($lession->tutor_time_id);
                if ($time && !$time->is_group) {
                    $time->booked = true;
                    $time->save();
                }
                // if tutor accepted the request and its a group
                if($lession->group){
                    $gr = GroupUser::where('user_id',$lession->student_id)
                    ->where('lesson_id',$lession->id)->first();
                    if($gr){    
                        $groupUser = $gr;
                        $gr->allowed = true;
                        $gr->save();
                    }
                }
            }
            $notification = new Notifications();
            $notification->user_id = $group ? $groupUser->user_id ?? $lession->student_id  : $lession->student_id;
            $notification->user_from = $lession->tutor_id;
            $notification->title = 'Lesson ' . ucfirst($request->status);
            $notification->body = $group ? "Group Lesson" : 'Tutor: ' . $lession->tutor->name;
            $notification->notification_time = Carbon::parse($lession->start_at, $lession->student->time_zone)->setTimezone('UTC');
            $notification->data = ['id' => $lession->id, 'type' => 'lession'];
            $notification->save();
            Fcm::sendNotification($notification);

            if ($request->status != 'approved') {
                $notification = new Notifications();
                $notification->user_id = $lession->tutor_id;
                $notification->user_from = $group ? ($groupUser->user_id ?? $lession->student_id)  :  $lession->student_id;
                $notification->title = 'Lesson ' . ucfirst($request->status);
                $notification->body =  $group ? "Group Lesson" : 'Student: ' . $lession->student->name;
                $notification->data = ['id' => $lession->id, 'type' => 'lession'];
                $notification->notification_time = Carbon::parse($lession->start_at, $lession->tutor->time_zone)->setTimezone('UTC');
                $notification->save();
                Fcm::sendNotification($notification);
            }
        } else {
            $time = '';
            $sendfromId = null;
            if ($user->id == $lession->student_id) {
                $sendToId = $lession->tutor_id;
                $sendfromId = $lession->student_id;
                $body =  $group ? "Group Lesson" : 'Stduent: ' . $lession->student->name;
                $time = Carbon::parse($lession->start_at, $lession->tutor->time_zone)->setTimezone('UTC');
            } else {
                $sendToId = $lession->student_id;
                $sendfromId = $lession->tutor_id;
                $body =  $group ? "Group Lesson" : 'Tutor: ' . $lession->tutor->name;
                $time = Carbon::parse($lession->start_at, $lession->student->time_zone)->setTimezone('UTC');
            }
            $notification = new Notifications();
            $notification->user_id = $sendToId;
            $notification->user_from = $sendfromId;
            $notification->title = 'Lesson ' . ucfirst($request->status);
            $notification->body = $body;
            $notification->notification_time = $time;
            $notification->data = ['id' => $lession->id, 'type' => 'lession'];
            $notification->save();
            Fcm::sendNotification($notification);
        }
        $lession = Lession::with(['notes', 'libraries', 'videos', 'tutor', 'times', 'student', 'instrument','group.user'])->find($id);
        return response(['status' => true, 'data' => $lession]);
    }

    public function destroy($id)
    {
        //
    }

    public function addNote(Request $request)
    {
        $request->validate([
            'lession_id' => 'required',
            'note' => 'required',
        ]);
        $note = new LessionNotes();
        $note->lession_id = $request->lession_id;
        $note->note = $request->note;
        $note->save();
        return response(['status' => true]);
    }

    public function addVideo(Request $request)
    {
        try {
            LessionVideos::create(['lession_id', $request->lession_id, 'video_url' => $request->video]);
            return response(['status' => true]);
        } catch (Exception $ex) {
            return response()->json(['status' => false, 'error' => $ex->getMessage()], 204);
        }
    }

    public function addMusicSheet(Request $request)
    {
        try {
            if ($request->hasFile('music_sheet')) {
                $file = $request->music_sheet;
                $name = time() . '.' . $file->getClientOriginalExtension();
                $file->move('documents', $name);
            }
            return response(['status' => true]);
        } catch (Exception $ex) {
            return response()->json(['status' => false, 'error' => $ex->getMessage()], 204);
        }
    }

    public function updateMusicSheets(Request $request)
    {
        $lession = Lession::find($request->lessionId);
        if ($request->has('libraries')) {
            $lession->libraries()->sync($request->libraries);
        }
        return response(['status' => true]);
    }


    public function submitReview(Request $request)
    {
        $review = new Review();
        $review->lession_id = $request->lession_id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->video_rating = $request->video_rating;
        $review->sound_rating = $request->sound_rating;
        if ($request->user()->hasRole('tutor')) {
            $review->rating_from = 'tutor';
        }
        $review->save();
        return $this->update($request, $request->lession_id);
    }

    public function addLessionTime(Request $request)
    {
        $auth = auth()->user();
        $fromDateTime = Carbon::parse($request->start, $auth->time_zone)->setTimezone('UTC');
        $toDateTime = Carbon::parse($request->end, $auth->time_zone)->setTimezone('UTC');

        $log = new LessionLogs;
        $log->lession_id = $request->lession_id;
        $log->start_time = $fromDateTime;
        $log->end_time = $toDateTime;
        $log->save();
        return response()->json(['status' => true, 'data' => $log]);
    }

    public function updateGroupUser(Request $request)
    {
        $group = GroupUser::find($request->id);
        if($group){
            $group->allowed = !$group->allowed;
            $time = Carbon::parse($group->lesson->start_at, $group->user->time_zone)->setTimezone('UTC');
            $group->save();
            $notification = new Notifications();
            $notification->user_id = $group->user_id;
            $notification->user_from = $group->lesson->tutor_id ?? 1;
            $notification->title = 'Lesson approved';
            $notification->body = 'Group Lesson';
            $notification->notification_time = $time;
            $notification->data = ['id' => $group->lesson_id, 'type' => 'lession'];;
            $notification->save();
            Fcm::sendNotification($notification);
        
            $lesson = Lession::find($group->lesson_id);
            if($lesson && $lesson->status == 'pending'){
                $lesson->status = 'approved';
                $lesson->save();
            }
        }
        return $this->show($request->lesson_id);
    }


    public function acceptAllRequest(Request $request) {
        $lessons = Lession::where('tutor_id', $request->user()->id)->where('status', 'pending')
        ->where('fee_paid', true)->get();

        $accept = $request->accept ?? false;
        foreach ($lessons as $lession) {

            // Delete all the request notifications for this lesson
            Notifications::whereJsonContains('data->id', $lession->id)
                ->whereJsonContains('data->type','lession')
                ->delete();
            $lession->status = $accept ? 'approved' : 'canceled';
            $lession->save();
            // update the tutor avaialble time
            $time = Carbon::parse($lession->start_at, 'UTC')->setTimezone($lession->tutor->time_zone);
            $time = TutorTime::find($lession->tutor_time_id);
            if ($time && !$time->is_group) {
                $time->booked = $accept;
                $time->save();
                // if cancel the lesson return the payment to student
                if($accept == false){
                    $rate = Currency::whereName($lession->currency)->first();
                    $payFee = $lession->fee;
                    if($rate){
                        $payFee = $payFee / $rate->rate;
                    }
                    $metadata = [
                        'tx_amount' => $payFee,
                        'tx_currency' => $lession->currency,
                    ];
                    $lession->student->updateBalance($payFee, $lession->tutor_id, 'Lesson canceled', true, $metadata);
                }
            }
            // if tutor accepted the request and its a group
            $gr = null;
            if($lession->instrument_id  == 21){
                $gr = GroupUser::where('user_id',$lession->student_id)
                ->where('lesson_id',$lession->id)->first();
                if($gr){    
                    $gr->allowed = $accept;
                    $gr->save();

                    // if cancel the lesson return the payment to student
                    if($accept == false){
                        $rate = Currency::whereName($gr->currency)->first();
                        $payFee = $gr->fee;
                        if($rate){
                            $payFee = $payFee / $rate->rate;
                        }
                        $metadata = [
                            'tx_amount' => $payFee,
                            'tx_currency' => $gr->currency,
                        ];
                        $gr->user->updateBalance($payFee, $lession->tutor_id, 'Lesson canceled', true, $metadata);
                    }
                }
            }
            
            // Send notification to student
            $notification = new Notifications();
            $notification->user_id = $lession->instrument_id  == 21 ? $gr->user_id :  $lession->student_id;
            $notification->user_from = $lession->tutor_id;
            $notification->title = 'Lesson ' . ucfirst($lession->status);
            $notification->body = $lession->instrument_id == 21 ? "Group Lesson" : 'Tutor: ' . $lession->tutor->name;
            $notification->notification_time = Carbon::parse($lession->start_at, $lession->student->time_zone)->setTimezone('UTC');
            $notification->data = ['id' => $lession->id, 'type' => 'lession', 'status' => $accept ? 'approved' : 'canceled'];
            $notification->save();
            Fcm::sendNotification($notification);
        }
        $data = ['message' => 'All lessons accepted', 'count' => $lessons->count()];
        return response()->json($data, 200);
    }


    function removeUnpaidLessons(Request $request) {

        Lession::whereIn('id', $request->ids)->where('fee_paid', false)->where('status', 'pending')->delete(); 
        GroupUser::whereIn('id', $request->gids)->where('fee_paid', false)->delete();
        return response()->json(['message' => 'Lessons removed'], 200);
    }
}
