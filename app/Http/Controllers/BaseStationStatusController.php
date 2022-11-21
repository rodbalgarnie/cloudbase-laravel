<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BaseStationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\BaseStationStatusResource as BaseStationStatusResource;

class RTKStatusController extends Controller
{

	
   public function index(Request $request)
    {
	   $status = BaseStationStatus::get();
	   return array('BaseStationStatus'=>BaseStationStatusResource::collection($status));//
    }

  
    public function store(Request $request)
    {
		
        $RTKStatus = $request->isMethod('put') ? BaseStationStatus::findorfail($request->value) : new BaseStationStatus;
		$RTKStatus->id = $request->value;
		$RTKStatus->title = $request->text;
		
		if($RTKStatus->save())
		{
			return new BaseStationStatusResource($RTKStatus);
		}    
	}

   
    public function show($id)
    {
        $RTKStatus = BaseStationStatus::findorfail($id);
		return new BaseStationStatusResource($RTKStatus);
    }


    public function destroy($id)
    {
        $RTKStatus = BaseStationStatus::findorfail($id);
		if($RTKStatus->delete()){
			return new BaseStationStatusResource($RTKStatus);
		}
    }
}
