<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\CasterEventType;
use App\Http\Controllers\Controller; 
use App\Http\Resources\CasterEventTypeResource as CasterEventTypeResource;

class CasterEventTypesController extends Controller
{
	
   public function index(Request $request)
    {
	   $group = $request->group;
	   
	   $types = CasterEventType::
	   when($group != '', function ($q) use($group) {
					return $q->where('group',$group);
			})	
		->get();
	   return array('CasterEventTypes'=>CasterEventTypeResource::collection($types));//
    }
	
	public function store(Request $request)
    {
		$event = $request->isMethod('put') ? CasterEventType::findorfail($request->value) : new CasterEventType;
		$event->id = $request->value;
		$event->text = $request->text;
	
		$event->save();
		return new CasterEventTypeResource($event);
	}
	
	
    public function destroy($id)
    {
        $event = CasterEvent::findorfail($id);
		$event->delete();
		return;
    }
}
