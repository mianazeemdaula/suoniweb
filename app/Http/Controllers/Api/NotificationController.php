<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = Notifications::with(['user', 'userfrom'])->where('user_id', $user->id)
        ->where('queued', false)->orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $data = Notifications::find($id);
        $data->is_read = true;
        $data->save();
        return $this->index();
    }

    public function destroy($id)
    {
        $data = Notifications::find($id)->delete();
        return $this->index();
    }

    public function deleteAll(Request $request)
    {
        $user = Auth::user();
        Notifications::where('user_id', $user->id)->delete();
        return $this->index();
    }
}
