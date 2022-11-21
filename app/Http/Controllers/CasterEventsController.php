<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\CasterEvent;
use App\CasterEventType;
use App\CasterDealer;
use App\CasterCompany;
use App\Rover;
use App\CasterNtripSubscription;
use App\User;
use App\Http\Controllers\Controller; 
use App\Http\Resources\CasterEventResource as CasterEventResource;


class CasterEventsController extends Controller
{
	
   public function index(Request $request)
    {
	   $id = $request->id;
	   $reseller = $request->reseller;
	   $dealer = $request->dealer;
	   $company = $request->company;
	   $rover = $request->rover;
	   $user = $request->user;
	   $type = $request->type;
	   $group = $request->group;
	   $base = $request->base;
	   $offset = $request->offset;
	   $limit = $request->limit;
	   
	   $events = CasterEvent::
	   	when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
		->when($reseller> 0, function ($q) use($reseller) {
					return $q->where('reseller',$reseller);
			})		
		->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})	
		->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})	
		->when($rover > 0, function ($q) use($rover) {
					return $q->where('rover',$rover);
			})	
		->when($user > 0, function ($q) use($user) {
					return $q->where('user',$user);
			})
		->when($type > 0, function ($q) use($type) {
					return $q->where('type',$type);
			})
		->when($group != '', function ($q) use($group) {
					return $q->where('eventgroup',$group);
			})
		->when($base != 0, function ($q) use($base) {
					return $q->where('basestation',$base);
			})
		->orderBy('id','DESC')	
		->skip($offset)	
		->take($limit)	
		->get();
	   
	   $eventstotal = CasterEvent::
	   	when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
		->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})	
		->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})	
		->when($rover > 0, function ($q) use($rover) {
					return $q->where('rover',$rover);
			})	
		->when($user > 0, function ($q) use($user) {
					return $q->where('user',$user);
			})
		->when($type > 0, function ($q) use($type) {
					return $q->where('type',$type);
			})
		->when($group != '', function ($q) use($group) {
					return $q->where('eventgroup',$group);
			})
		->when($base != 0, function ($q) use($base) {
					return $q->where('basestation',$base);
			})
		->count();
		
	   
	   return array('count'=>$eventstotal,'CasterEvents'=>CasterEventResource::collection($events));//
    }
	
	public function store(Request $request)
    {
		$event = $request->isMethod('put') ? CasterEvent::findorfail($request->value) : new CasterEvent;
		$event->id = $request->value;
		$event->type = $request->type;
		$event->dealer = $request->dealer;
		$event->company = $request->company;
		$event->rover = $request->rover;
		$event->user = $request->user;
		$event->email1 = $request->email1;
		$event->email2 = $request->email2;
		$event->save();
		return new CasterEventResource($event);
	}
	
	public function userevent(Request $request)
	{
		$user = User::where('id',$request->user)->first();
		
		$group = CasterEventType::where('id',$request->type)->first();
		if($group !== ''){
			$eventgroup = $group->group;
		} else $eventgroup = '-';
		
		$event = new CasterEvent;
		$event->type = $request->type;
		$event->eventgroup = $eventgroup;
		$event->text = $request->text;
		$event->reseller = $user->business;
		$event->dealer = $user->dealer;
		$event->company = $user->company;
		$event->user = $request->user;
		$event->save();
		return;
	}

    public function destroy($id)
    {
        $event = CasterEvent::findorfail($id);
		$event->delete();
		return;
    }
}
