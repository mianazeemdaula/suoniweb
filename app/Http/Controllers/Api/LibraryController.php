<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Library;
use App\Models\Lession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LibraryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = Library::where('student_id', $user->id)->get();
        return response()->json(['status' => true, 'data' => $data]);
        $data = User::withAndWhereHas('libraries', function ($q) use ($user) {
            $q->where('is_lession', 1);
            $q->orWhere('student_id', $user->id);
        })->get();
        return response()->json(['status' => true, 'data' => $data]);
    }


    public function create()
    {
    }


    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3',
            'doc' => 'required|mimes:pdf,jpg,png,jpeg',
        ]);
        if ($validator->fails()) {
            return response()->json(['required' => $validator->errors()->first()], 200);
        } else {
            $fileName = '';
            if ($request->hasFile('doc')) {
                $file = $request->doc;
                $name = time() . '.' . $file->getClientOriginalExtension();
                $file->move('documents', $name);
                $fileName = 'documents/' . $name;
            }
            $lib = new Library();
            $lib->student_id = $request->user()->id;
            $lib->title = $request->title;
            $lib->is_lession = $request->has('is_lession') ?  $request->is_lession : false;
            $lib->doc = $fileName;
            $lib->save();
            $data = User::has('libraries')->with('libraries')->get();
            return response()->json(['status' => true, 'data' => $data]);
        }
    }
    public function show($id)
    {
        $data = Library::where('student_id', Auth::user()->id)->get();
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function edit($id)
    {
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        $data =  Library::find($id)->delete();
        return response()->json(['status' => $data]);
    }

    public function mixLabs(Request $request)
    {
        $user1 = $request->user()->id;
        $user2 = $request->user_id;
        $libUsers1 = Lession::where('student_id', $user1)->orWhere('tutor_id', $user1)->get();
        $libUsers2 = Lession::where('student_id', $user2)->orWhere('tutor_id', $user2)->get();
        $ids = [];
        foreach ($libUsers1 as $a) {
            $ids[] = $a->student_id;
            $ids[] = $a->tutor_id;
        }
        foreach ($libUsers2 as $a) {
            $ids[] = $a->student_id;
            $ids[] = $a->tutor_id;
        }
        array_unique($ids);
        $data = User::withAndWhereHas('libraries', function ($q) use ($user1) {
            $q->where('is_lession', 1);
            $q->orWhere('student_id', $user1);
        })->where('id', $user1)->get();
        return response()->json(['status' => true, 'data' => $data]);
    }
}
