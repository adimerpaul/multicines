<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebController extends Controller{
    function movies(){
        return view('movies');
    }
}
