<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CasterSubscription;
use App\CasterSubscriptionTEST;
use App\CasterNtripSubscription;
use App\Http\Controllers\Controller;
use App\Http\Resources\CasterSubscriptionResource as CasterSubscriptionResource;

class CasterSubscriptionsController extends Controller
{
	
//	 public function test(Request $request) CREATE TEST SUBS
//    {
//		$count = 1;
//		 
//		while($count !== 2001){ 
//		$username = 'user'.$count;
//		$password = 'password'.$count;
//		 
//        $sub = new CasterSubscriptionTEST;
//		$sub->Username = $username;
//		$sub->Password = $password;
//		$sub->Expiry_date = '2023-04-05 00:00:00';
//		$sub->Allowed = 1;
//		$sub->save();
//			
//		$count++;
//		}
//		
//	}
	
	
   public function index(Request $request)
    {
	   $id = $request->id;
	   $subs = CasterSubscription::
	   	when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
		
		->get();
	   return array('CasterSubscriptions'=>CasterSubscriptionResource::collection($subs));//
    }

	
	public function indextotals(Request $request)
    {
	   	$dealer  = $request->dealer;
		$company = $request->company;
		$status = $request->status;
		$expired = $request->expired;
		if($expired == 0){$status2 = 1;$status = 0; } else $status2 = 0;
		$stock = 0;
		$totals = [];
		
	   	$subtypes = CasterSubscription::select('type','title')->get();
		
		
		foreach($subtypes as $subtype){
			
			$subs = CasterNtripSubscription::
	   		when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})
			->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})
			->when($status2 > 0, function ($q) {
					return $q->where('status','!=',4);
			})		
			->when($status > 0, function ($q) use($status) {
					return $q->where('status',$status);
			})	
			->where('type',$subtype->type)	
			->where('stock',$stock)
			->get();
			
			$count = count($subs);
			if($count > 0){
				$string = $subtype->title.' ('.$count.')';
				$totals[] = array('value'=>$subtype->type,'text'=>$string);
			}
		}
		
		return $totals;
		
    }
  
    public function store(Request $request)
    {
		
        $sub = $request->isMethod('put') ? CasterSubscription::findorfail($request->id) : new CasterSubscription;
		$sub->id = $request->id;
		$sub->title = $request->title;
		$sub->price = $request->price;
		$sub->years = $request->years;
		$sub->months = $request->months;
		$sub->days = $request->days;
		$sub->save();
		return new CasterSubscriptionResource($sub);
	}

   
    public function show($id)
    {
        $sub = CasterSubscription::findorfail($id);
		return new CasterSubscriptionResource($sub);
    }


    public function destroy($id)
    {
        $sub = CasterSubscription::findorfail($id);
		if($sub->delete()){
			return new CasterSubscriptionResource($sub);
		}
    }
}
