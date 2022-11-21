<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CasterBusinessDealer;
use App\Http\Controllers\Controller;
use App\Http\Resources\CasterBusinessDealerResource as CasterBusinessDealerResource;

class CasterBusinessDealerController extends Controller 
{

	
   public function index(Request $request)
    {
	   $id = $request->id;
	   $users = CasterBusinessDealer:: 
	   	when($id > 0, function ($q) use($id) {
					return $q->where('business',$id);
			})
		->get();
	   return array('CasterBusinessDealers'=>CasterBusinessDealerResource::collection($users));//
    }
	
	
	public function store(Request $request)
    {
		
        $user = $request->isMethod('put') ? CasterBusinessDealer::findorfail($request->value) : new CasterBusinessDealer;
		$user->id = $request->value;
		$user->business = $request->business;
		$user->dealer = $request->dealer;
		return $user;
	}
	
	 public function archive(Request $request)
    {
	    $dealer = CasterBusinessDealer::where('id',$request->id)->delete();
		$dealer->save();
		return;
	 }


}
