<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RTKStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\RTKStatusResource as RTKStatusResource;

class RTKStatusController extends Controller
{

	
   public function index(Request $request)
    {
	   $status = RTKStatus::get();
	   return array('RTKStatus'=>RTKStatusResource::collection($status));//
    }

  
    public function store(Request $request)
    {
		
        $RTKStatus = $request->isMethod('put') ? RTKStatus::findorfail($request->value) : new RTKStatus;
		$RTKStatus->id = $request->value;
		$RTKStatus->title = $request->text;
		
		if($RTKStatus->save())
		{
			return new RTKStatusResource($RTKStatus);
		}    
	}

   
    public function show($id)
    {
        $RTKStatus = RTKStatus::findorfail($id);
		return new RTKStatusResource($RTKStatus);
    }


    public function destroy($id)
    {
        $RTKStatus = RTKStatus::findorfail($id);
		if($RTKStatus->delete()){
			return new RTKStatusResource($RTKStatus);
		}
    }
}
