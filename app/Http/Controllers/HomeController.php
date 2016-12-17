<?php

namespace App\Http\Controllers;

use App\Statis;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use phpari;
use sounds;
use applications;
use channels;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        return view('home', compact('user'));
        //return dd($user);
    }

    public function connect()
    {

    }
}
