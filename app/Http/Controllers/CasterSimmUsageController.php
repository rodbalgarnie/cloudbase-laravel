<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\CasterSimmUsage;
use App\Http\Controllers\Controller;
use App\Http\Resources\CasterSimmUsageResource as CasterSimmUsageResource;

class CasterSimmUsageController extends Controller  
{

   public function index(Request $request)
    {
	   $id = $request->id;
	   
	   $simms = CasterSimmPackage::
	   	when($id > 0, function ($q) use($id) {
					return $q->where('package_id',$id);
			})
		->get();
	   return array('CasterSimmPackage'=>CasterSimmPackageResource::collection($simms));//
    }

  
}
