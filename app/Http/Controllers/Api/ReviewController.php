<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lession;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class ReviewController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];
        if ($user->hasRole('tutor')) {
            $ids = Lession::where('tutor_id', $user->id)->get()->pluck('id');
            $data = Review::whereIn('id', $ids)->with(['student'])->get();
        } else if ($user->hasRole('student')) {
            $ids = Lession::where('student_id', $user->id)->get()->pluck('id');
            $data = Review::whereIn('id', $ids)->with(['tutor'])->get();
        }
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        try {
            $review = new Review();
            $review->lession_id = $request->lession_id;
            $review->rating = $request->rating;
            $review->comment = $request->comment;
            $review->video_rating = $request->video_rating;
            $review->sound_rating = $request->sound_rating;
            if($request->user()->hasRole('tutor')){
                $review->rating_from = 'tutor';
            }
            $review->save();
            return response()->json(['status' => true, 'data' => $review]);
        } catch (Exception $e) {
            return response()->json(['status' => true, 'data' => $e->getMessage()]);
        }
    }
}
