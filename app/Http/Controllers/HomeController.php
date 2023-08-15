<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Instrument;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $users = User::count();
        $instruments  = Instrument::count();
        $categories  = 0;
        $lessions = 0;
        return view('home', compact('users', 'instruments', 'categories', 'lessions'));
    }
}
