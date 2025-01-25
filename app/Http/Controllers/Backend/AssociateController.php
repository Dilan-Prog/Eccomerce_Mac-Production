<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AssociateController extends Controller
{
    public function dashboard(){

        return view('associate.dashboard');

    }
}
