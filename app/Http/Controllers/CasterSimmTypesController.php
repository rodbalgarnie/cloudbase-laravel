<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\CasterSimmType;
use App\Http\Controllers\Controller;
use App\Http\Resources\CasterSimmTypeResource as CasterSimmTypeResource;

class CasterSimmTypesController extends Controller 
{

   public function index(Request $request)
    {
	   $id = $request->id;
	   
	   $simms = CasterSimmType::
	   	when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
		->get();
	   return array('CasterSimmTypes'=>CasterSimmTypeResource::collection($simms));//
    }

  
    public function store(Request $request)
    {
		
        $simm = $request->isMethod('put') ? CasterSimmType::findorfail($request->value) : new CasterSimmType;
		$simm->id = $request->value;
		$simm->title = $request->text;
		$simm->save();
		return new CasterSimmTypeResource($simm);
	}

   
    public function show($id)
    {
        $simm = CasterSimmType::findorfail($id);
		return new CasterSimmTypeResource($simm);
    }


    public function destroy($id)
    {
        $simm = CasterSimmType::findorfail($id);
		if($simm->delete()){
			return new CasterSimmTypeResource($simm);
		}
    }
}
