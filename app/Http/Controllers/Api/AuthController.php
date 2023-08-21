<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use App\Mail\VerifyApiEmail;
use App\Models\Tutor;
use App\Models\Student;
use App\Models\TutorVideos;
use App\Models\PasswordReset;

use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6',
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|min:4|max:150',
        ]);
        if ($validator->fails()) {
            return response()->json(['required' => $validator->errors()->first()], 200);
        } else {
            $user = new User;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->name = $request->name;
            $user->time_zone = $request->time_zone;
            $user->save();
            if ($request->tutor == true) {
                $user->assignRole('tutor');
                $tutor = new Tutor();
                $tutor->bio = '';
                $tutor->in_search = false;
                $tutor->save();
                $tutor->user()->save($user);
                $user->status = 'incomplete';
                $user->save();
                $token = $user->createToken('Auth Token')->plainTextToken;
                $user = $user->getTutor($user->id);
                return response()->json(['token' => $token, 'user' => $user], 200);
            } else {
                $user->assignRole('student');
                $student = new Student();
                $student->save();
                $student->user()->save($user);
                $token = $user->createToken('Auth Token')->plainTextToken;
                return response()->json(['token' => $token, 'user' => $user], 200);
            }
        }
    }

    public function registerApple(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'required|string|min:4|max:150',
        ]);
        if ($validator->fails()) {
            return response()->json(['required' => $validator->errors()->first()], 200);
        } else {
            $user = User::withTrashed()->where('email', $request->email)->first();
            if (!$user->exists()) {
                $user = new User;
                $user->email = $request->email;
                // $user->password = bcrypt($request->password);
                $user->name = $request->name;
                $user->time_zone = $request->time_zone;
                $user->save();
            }else{
                if ($user->trashed()) {
                    $user->restore();
                }
                $token = $user->createToken('Auth Token')->plainTextToken;
                if ($user->hasRole('tutor'))
                    $user = $user->getTutor($user->id);
                return response()->json(['token' => $token, 'user' => $user], 200);
            }
            
            if ($request->tutor == true) {
                $user->assignRole('tutor');
                $tutor = new Tutor();
                $tutor->bio = '';
                $tutor->in_search = false;
                $tutor->save();
                $tutor->user()->save($user);
                $user->status = 'incomplete';
                $user->save();
                $token = $user->createToken('Auth Token')->plainTextToken;
                $user = $user->getTutor($user->id);
                return response()->json(['token' => $token, 'user' => $user], 200);
            } else {
                $user->assignRole('student');
                $student = new Student();
                $student->save();
                $student->user()->save($user);
                $token = $user->createToken('Auth Token')->plainTextToken;
                return response()->json(['token' => $token, 'user' => $user], 200);
            }
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|string',
            'password' => 'required|string|min:4',
        ]);
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $user = User::where('email', $request->email)->first();
        if ($user !=null && $user->trashed()) {
            return response()->json(['message' => 'Email not found'], 204);
        }
        if ($user) {
            if (Auth::attempt($credentials)) {
                $token = $user->createToken('Auth Token')->plainTextToken;
                if ($user->hasRole('tutor'))
                    $user = $user->getTutor($user->id);
                return response()->json(['token' => $token, 'user' => $user], 200);
            }
            return response()->json(['messasge' => 'Wrong credientals'], 401);
        } else {
            return response()->json(['message' => 'Email not found'], 204);
        }
    }



    public function sociallogin(Request $request)
    {
        try {
            $user = Socialite::driver($request->driver)->userFromToken($request->token);
            $authUser = User::where('email', $user->getEmail())->first();
            if ($authUser->trashed()) {
                $authUser->restore();
            }
            if ($authUser) {
                Auth::login($authUser);
                $token = $authUser->createToken('Auth Token')->plainTextToken;
                return response()->json(['token' => $token, 'user' => $authUser], 200);
            } else {
                $authUser = new User;
                $authUser->name = $user->name;
                $authUser->email = $user->email;
                $authUser->social_id = $user->id;
                $authUser->email_verified_at = Carbon::now();
                $authUser->image = $user->getAvatar();
                $authUser->save();
                $authUser->assignRole('student');
                $student = new Student();
                $student->save();
                $student->user()->save($authUser);
                Auth::login($authUser);
                $token = $authUser->createToken('Auth Token')->plainTextToken;
                return response()->json(['token' => $token, 'user' => $authUser], 200);
            }
        } catch (Exception $ex) {
            return response()->json(['error' => $ex], 204);
        }
    }

    public function socialcallback(Request $request)
    {
        return $request->all();
    }

    public function sendPasswordResetEmail(Request $request)
    {
        $credentials = ['email' => $request->email];
        $response = Password::sendResetLink($credentials, function (Message $message) {
            $message->subject($this->getEmailSubject());
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
                return response()->json(['messasge' => 'Reset password link sent succesfully'], 200);
            case Password::INVALID_USER:
                return response()->json(['messasge' => 'Invalid user'], 204);
        }
    }

    public function updateAvatar(Request $request)
    {
        $user = Auth::user();
        if ($request->has('image')) {
            $name = 'images/' . Str::random(40) . '.' . $request->image->getClientOriginalExtension();
            Image::make($request->image)->resize(250, 250, function ($constraint) {
                $constraint->aspectRatio();
            })->save($name);
            $user->image = $name;
        }
        $user->save();
        if ($user->hasRole('tutor'))
            $user = $user->getTutor($user->id);
        return response()->json(['status' => true, 'data' => $user], 200);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            $isTutor = $user->hasRole('tutor');
            if ($request->has('fcm_token')) {
                $user->fcm_token = $request->fcm_token;
            }
            if ($request->has('in_search')) {
                $user->userable()->update(['in_search' => $request->in_search]);
            }
            if ($request->has('clock_24')) {
                $user->clock_24 = $request->clock_24;
            }
            if ($request->has('time_zone')) {
                $user->time_zone = $request->time_zone;
            }
            $user->save();
            if ($isTutor) {
                $user = $user->getTutor($user->id);
            } else {
                $user = User::find($user->id);
            }
            return response()->json(['status' => true, 'data' => $user], 200);
        } catch (Exception $ex) {
            return response()->json(['messasge' => $ex], 500);
        }
    }

    public function saveAsTutor(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'instruments' => 'required',
                // 'categories' => 'required',
                'bio' => 'required|string|min:4',
            ]);
            if ($validator->fails()) {
                return response()->json(['required' => $validator->errors()->first()], 200);
            }
            $user = $request->user();
            if ($request->has('video_url') && $request->video_url != null) {
                $video = new TutorVideos;
                $video->url = $request->video_url;
                $video->tutor_id = $user->id;
                $video->save();
            }
            $user->status = 'active';
            $user->save();
            $user->userable()->update(['bio' => $request->bio]);
            // $user->instrumentCats()->sync($request->categories);
            $user->instruments()->sync($request->instruments);
            if ($user->has('languages')) {
                $user->languages()->sync($request->languages);
            }
            $user = $user->getTutor($user->id);
            return response()->json(['status' => true, 'data' => $user], 200);
        } catch (Exception $ex) {
            return response()->json(['status' => $ex], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $request->user()->delete();
            return response()->json(['status' => true, 'message' => 'account deleted successfully'], 200);
        } catch (Exception $ex) {
            return response()->json(['status' => false, 'message' => $ex], 500);
        }
    }

    public function sendResetPasswordPin(Request $request)
    {
        $code = rand(100000,999999);
        $user =  User::where('email', $request->email)->first();
        if(!$user){
            return  response()->json(['message' => 'Email not exists'], 204);
        }
        $data = PasswordReset::where('email', $request->email)->first();
        if($data){
            PasswordReset::where('email', $request->email)->delete();
        }
        PasswordReset::insert([
            'email' => $request->email,
            'token' => $code,
            'created_at' => now(),
        ]);
        Mail::to($request->email)->send(new VerifyApiEmail($code));
        return response()->json(['message' => 'Email sent successfully'], 200);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string|exists:password_resets',
            'password' => 'required',
            'email' => 'required',
        ]);
        $data = PasswordReset::where('email', $request->email)->first();
        if($data){
            if ($data->created_at->addMinutes(15) < now()) {
                PasswordReset::where('email', $request->email)->delete();
                return response(['message' => trans('passwords.code_is_expire')], 422);
            }
            $user = User::where('email', $request->email)->first();
            $user->password = bcrypt($request->password);
            $user->save();
            PasswordReset::where('email', $user->email)->delete();
            return response()->json(['message'=> 'Password reset successfully'], 200);
        }
        return response()->json(['message'=> 'email verification not in process'], 409);
    }
}
