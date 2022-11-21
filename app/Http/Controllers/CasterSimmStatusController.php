<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\CasterSimmStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CasterSimmStatusResource as CasterSimmStatusResource;

class CasterSimmStatusController extends Controller 
{

   public function index(Request $request)
    {
	   $id = $request->id;
	   
	   $simms = CasterSimmStatus::
	   	when($id > 0, function ($q) use($id) {
					return $q->where('package_id',$id);
			})
		->get();
	   return array('CasterSimmStatus'=>CasterSimmStatusResource::collection($simms));//
    }

  
}
