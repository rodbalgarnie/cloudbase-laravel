<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Job;
use App\System;
use App\Http\Controllers\Controller;


class SystemController extends Controller
{

	
   public function getpollstatus()
    {
	   $data = System::first();
	   return $data;
    }


}
