<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MachineModel;
use App\Machine;
use App\Http\Controllers\Controller;
use App\Http\Resources\MachineModelResource as MachineModelResource; 
use App\Http\Resources\MachineModelShortResource as MachineModelShortResource; 

class MachineModelsController extends Controller
{

	
   public function index(Request $request) 
    {
	   $stext = $request->stext;
	   $title = $request->title;
	   $maker = $request->maker;
	   
	   $models = MachineModel::
	   	when($title != '', function ($q) use($title) {
				return $q->where('title',$title);
			})
	   	->when($maker != 0, function ($q) use($maker) {
				return $q->where('maker',$maker);
			})	   
	   	->when($stext != '', function ($q) use ($stext){
    			return $q->where('title','LIKE','%'.$stext.'%');
			})
		->orderBy('title','ASC')		   
	   	->get();
	   
	   if($stext != '' || $title != ''){
		  return array('models'=>MachineModelShortResource::collection($models));// 
	   } else 
	   return array('models'=>MachineModelResource::collection($models));//
    }

  
    public function store(Request $request)
    {
		$machine_id = 0;
		
	    $model = $request->isMethod('put') ? MachineModel::findorfail($request->value) : new MachineModel;
		$model->id = $request->value;
		$model->title = $request->text;
		$model->type = $request->type;
		$model->maker = $request->maker;
		$model->save();
	
		return $model->id;
	}

   
    public function show($id)
    {
        $model = MachineModel::findorfail($id);
		return new MachineModelResource($model);
    }


    public function destroy($id)
    {
        $model = MachineModel::findorfail($id);
		if($model->delete()){
			return new MachineModelResource($model);
		}
    }

}
