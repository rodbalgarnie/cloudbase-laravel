<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CasterDealerDepot;
use App\Http\Controllers\Controller;
use App\Http\Resources\CasterDealerDepotResource as CasterDealerDepotResource;

class CasterDealerDepotsController extends Controller
{

	
   public function index(Request $request)
    {
	   $id = $request->id;
	   $stext = $request->stext;
	   $dealer = $request->dealer;
	   
	   $depots = CasterDealerDepot::
	   	when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
		->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('maindealer',$dealer);
			})	
		->when($stext != '', function ($q) use($stext) {
				return $q->where('title','LIKE',$stext.'%');
					})
		->get();
	   return array('CasterDepots'=>CasterDealerDepotResource::collection($depots));//
    }

  
    public function store(Request $request)
    {
		
        $user = $request->isMethod('put') ? CasterDealerDepot::findorfail($request->value) : new CasterDealer;
		$user->id = $request->value;
		$user->title = $request->text;
		$user->email = $request->email;
		$user->address1 = $request->address1;
		$user->address2 = $request->address2;
		$user->address3 = $request->address3;
		$user->towncity = $request->towncity;
		$user->county = $request->county;
		$user->postcode = $request->postcode;
		$user->tel = $request->tel;
		$user->mobile = $request->mobile;
		$user->website = $request->website;
		$user->save();
		return new CasterDealerDepotResource($user);
	}

   
    public function show($id)
    {
        $user = CasterDealerDepot::findorfail($id);
		return new CasterDealerDepotResource($user);
    }


    public function destroy($id)
    {
        $user = CasterDealerDepot::findorfail($id);
		if($user->delete()){
			return new CasterDealerDepotResource($user);
		}
    }
}
