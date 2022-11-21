<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\CasterSimmPackageUsage;
use App\Http\Controllers\Controller;
use App\Http\Resources\CasterSimmPackageUsageResource as CasterSimmPackageUsageResource;

class CasterSimmPackageUsageController extends Controller 
{

   public function index(Request $request)
    {
	   $id = $request->id;
	   
	   $simms = CasterSimmPackageUsage::
	   	when($id > 0, function ($q) use($id) {
					return $q->where('package_id',$id);
			})
		->orderBy('id','DESC')	
		->limit(2)	
		->get();
	   return array('CasterSimmPackage'=>$simms);//
    } 

  
}
