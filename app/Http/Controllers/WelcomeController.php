<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Quote;

class WelcomeController extends Controller
{


    public function index()
    {
        return view('welcome');
    }


    public function submit()
    {

        if (session('security_flag')) {
            return redirect('/invalid');
        }

        return view('welcome');


    }
}
