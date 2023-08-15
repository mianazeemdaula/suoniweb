<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TutorVideos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class VideoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = TutorVideos::where('tutor_id', $user->id)->get();
        return response()->json($data);
    }

    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'url' => 'required|min:3',
        ]);
        if ($validator->fails()) {
            return response()->json(['required' => $validator->errors()->first()], 200);
        } else {
            $video = new TutorVideos();
            $video->tutor_id = $request->user()->id;
            $video->url = $request->url;
            $video->save();
            return response()->json(['status' => true, 'data' => $video]);
        }
    }

    public function destroy($id)
    {
        $data =  TutorVideos::find($id)->delete();
        return response()->json(['status' => $data]);
    }
}
