<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }
    
    public function doLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $credentials = $request->only('email', 'password');
        if (auth()->attempt($credentials)) {
            $user = auth()->user();
            return redirect()->intended('/');
        }
        return redirect()->back()->withErrors(['password' => 'Invalid Credentials']);
    }

    public function logout()
    {
        auth()->logout();
        return redirect()->route('login');
    }

    public function home()
    {
        return view('admin.home');
    }
}
