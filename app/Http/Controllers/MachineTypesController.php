<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MachineType;
use App\Http\Controllers\Controller;
use App\Http\Resources\MachineTypeResource as MachineTypeResource;

class MachineTypesController extends Controller
{

	
   public function index(Request $request) 
    {
	   $types = MachineType::get();
	   return array('types'=>MachineTypeResource::collection($types));//
    }
  
    public function store(Request $request)
    {
		
	    $type = $request->isMethod('put') ? MachineType::findorfail($request->value) : new MachineType;
		$type->id = $request->value;
		$type->title = $request->text;
		$type->save();
		return new MachineTypeResource($type);
	}

   
    public function show($id)
    {
        $type = MachineType::findorfail($id);
		return new MachineTypeResource($type);
    }


    public function destroy($id)
    {
        $type = MachineType::findorfail($id);
		if($type->delete()){
			return new MachineTypeResource($type);
		}
    }
}
