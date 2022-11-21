<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MachineMaker;
use App\Http\Controllers\Controller;
use App\Http\Resources\MachineMakerResource as MachineMakerResource;  

class MachineMakersController extends Controller
{

	
   public function index(Request $request) 
    {
	   $stext = $request->stext;
	   $title = $request->title;
	   
	   $makers = MachineMaker::
	   	when($title != '', function ($q) use($title) {
				return $q->where('title',$title);
			})
	   	->when($stext != '', function ($q) use ($stext){
    			return $q->where('title','LIKE','%'.$stext.'%');
			})
		->orderBy('title','ASC')	   
	   	->get();
	   return array('makers'=>MachineMakerResource::collection($makers));//
    }

  
    public function store(Request $request)
    {
		
	    $maker = $request->isMethod('put') ? MachineMaker::findorfail($request->value) : new MachineMaker;
		$maker->id = $request->value;
		$maker->title = $request->text;
		$maker->save();
		return $maker->id;
	}

   
    public function show($id)
    {
        $maker = MachineMaker::findorfail($id);
		return new MachineMakerResource($maker);
    }


    public function destroy($id)
    {
        $maker = MachineMaker::findorfail($id);
		if($maker->delete()){
			return new MachineMakerResource($maker);
		}
    }

}
