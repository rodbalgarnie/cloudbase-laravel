<?php
// Version 200722 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CasterNtripSubscriptionStock;
use App\CasterNtripSubscription;
use App\CasterSubscription;
use App\CasterSubStatus;
use App\CasterDealer;
use App\Http\Controllers\Controller;
use App\Http\Resources\CasterNtripSubscriptionStockResource as CasterNtripSubscriptionStockResource;
use App\Http\Resources\CasterNtripSubscriptionResourceStock as CasterNtripSubscriptionResourceStock;
use App\Http\Resources\CasterNtripSubscriptionResourceStock2 as CasterNtripSubscriptionResourceStock2;

class CasterNtripSubscriptionsStockController extends Controller 
{
	

   public function index(Request $request)
    {
	   $id = $request->id;
	   $business = $request->business;
	   $dealer = $request->dealer;
	   $company = $request->company;
	   $type = $request->type;
	   $status=$request->status;
	   
	  
	   $subs = CasterNtripSubscriptionStock::
	   	when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
		->when($business > 0, function ($q) use($business) {
					return $q->where('business',$business);
				})		
		->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})
		->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})	
		->when($type > 0, function ($q) use($type) {
					return $q->where('type',$type);
			})
		->when($status > 0, function ($q) use($status) {
					return $q->where('status',$status);
			})		
		->get();
	   
	   return array('CasterSubs'=>CasterNtripSubscriptionStockResource::collection($subs));//
    }
	
	
	
	
	public function gettotals(Request $request)
    {
		$business = $request->business;	
	   	$dealer = $request->dealer;
	   	$company = $request->company;
	   	$subsarray = [];
	   //if($company > 0){$status = 2;} else $status = 1; // No stock count 	
		$total = 0;
		
		
		$states = CasterSubStatus::where('id','!=',1)->get();
		
		foreach($states as $state){
		
	   	$subs = CasterNtripSubscriptionStock::
			when($business > 0, function ($q) use($business) {
					return $q->where('business',$business);
			})
			->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})
			->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})
			->where('status',$state->code)
			->where('rover','!=',0)	
			->get();
		 
		    $subsarray[$state->code]['label'] = $state->message;
		    $subsarray[$state->code]['value'] = count($subs);
			$subsarray[$state->code]['color'] = $state->colour;
			
			if(count($subs) > 0){$total = $total + count($subs);}
		   
	   };
		
		return array('total'=>$total,'data'=>$subsarray);
		
	}
	
	public function getstocksubs(Request $request)
    {
		$reseller = $request->reseller;	
	   	$dealer = $request->dealer;
	   	$company = $request->company;
	   	$subsarray = [];
	  	$subs = CasterSubscription::get();
		$dealersubs = [];
		
		if($request->admin == 1){
			
			foreach($subs as $sub){

						$subslist = CasterNtripSubscriptionStock::
							where('business',$reseller)
							->where('type',$sub->type)
							->where('dealer',0)
							->where('sub_id',0)		
							->get();

							if(count($subslist) > 0){
								$dealersubs[] = array('value'=>$sub->type,'text'=>$sub->title,'count'=>count($subslist));
							}
						}
			
			
		} else {
		
		$dealers = CasterDealer::
			when($reseller > 0, function ($q) use($reseller) {
					return $q->where('business',$reseller);
			})	
			->get();	
			
			foreach($dealers as $dealer){
				
				$subsarray = [];	

					foreach($subs as $sub){

						$subslist = CasterNtripSubscriptionStock::
							where('dealer',$dealer->id)
							->where('type',$sub->type)
							->where('sub_id',0)		
							->get();

							if(count($subslist) > 0){
								$subsarray[] = array('value'=>$sub->id,'text'=>$sub->title,'count'=>count($subslist));
							}
						}

				$dealersubs[] = array('id'=>$dealer->id,'dealer'=>$dealer->title,'subs'=>$subsarray);

	   	}
			
		}
		
		return $dealersubs;
		
	
		
		
	}
  
	public function getstocksubsdealer(Request $request)
    {
		
	   	$dealer = $request->dealer;
	   	$subsarray = [];
	  	$subs = CasterSubscription::get();
		$dealersubs = [];
		
			
			foreach($subs as $sub){

						$subslist = CasterNtripSubscriptionStock::
							where('type',$sub->type)
							->where('dealer',$dealer)
							->where('sub_id',0)	
							->get();

							if(count($subslist) > 0){
								$dealersubs[] = array('value'=>$sub->type,'text'=>$sub->title,'count'=>count($subslist));
							}
						}
	
		return $dealersubs;
		
	}
	
	
	
	 public function storestockreseller(Request $request)
    {
		$loop = 0;
		 
		while($loop < $request->number){
		
        $sub = new CasterNtripSubscriptionStock;
		$sub->business = $request->reseller;	
		$sub->dealer = 0;
		$sub->stock = 1;
		$sub->user = $request->user;
		$sub->purchase_order_reseller = $request->po;	
		$sub->type = $request->type;
		$sub->save();
		
		$loop++;	
		}
		
		
		return ;
	}
	
	 public function storestock(Request $request)
    {
		$loop = 0;
		 
		while($loop < $request->number){
		
        $sub = CasterNtripSubscriptionStock::
				where('business',$request->reseller)
				->where('dealer',0)	
				->where('stock',1)
				->where('type',$request->id)
				->first();	
					
		$sub->dealer = $request->dealer;
		$sub->user = $request->user;
		$sub->purchase_order_dealer = $request->po;	
		$sub->save();
			
		$loop++;	
		}
		
		
		return;
	}
	
	public function indexstock(Request $request)
    {
	   
	   $dealer = $request->dealer;
	   $type = $request->type;
	   $stock = $request->stock;
	   
	   $subs = CasterNtripSubscriptionStock::
	  
		when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})
		->when($type > 0, function ($q) use($type) {
					return $q->where('type',$type);
			})
		->where('stock',1)
		->get();
	  
		
//	   if(count($subs) == 0){return array('error'=>'no stock');}
		
	   return array('CasterSubs'=>CasterNtripSubscriptionResourceStock::collection($subs));//
    }
	
	public function indexstock2(Request $request)
    {
	   
	   	$dealer = $request->dealer;
		$rover = $request->rover;
		$renew = $request->renew;
		
		// Get current Rover subscription and unique renewal stock susbscriptions
		
		if($rover == 0){
	   
	   // Get just available stock Subscriptions 
		$subs = CasterNtripSubscriptionStock::
	  	where('dealer',$dealer)
		->where('stock',1)
		->orderBy('type','ASC')
		->get()
	  	->unique('type');
			
		
		return array('CasterSubs'=>CasterNtripSubscriptionResourceStock2::collection($subs));//
			
		}
		
		// Get current Rover subscription and unique stock susbscriptions
		if($rover != 0){
			
		if($request->renew == 1){	
			$subs = CasterNtripSubscriptionStock::	// Get current rover pending sub
			where('rover',$rover)
			->where('stock',0)
			->where('status',2)	
			->get();
			
		} else {
			$subs = CasterNtripSubscriptionStock::	// Get current rover live stock sub
			where('rover',$rover)
			->where('status',3)		
			->where('stock',0)	
			->get();
		}
			
			
		if(count($subs) != 0){	
			$type = $subs[0]['type'];
		} else {
			$subs = [];
			$type = 0;
		}
			
		$subs2 = CasterNtripSubscriptionStock::	// get remaining stock subs excluding live one if set
			where('dealer',$dealer)
			->where('stock',1)
			->where('type','!=',$type)	
			->orderBy('type','ASC')
			->get()
	  		->unique('type');
			
		foreach($subs2 as $sub){
			$subs[] = $sub;
		}	
			
			
		return array('CasterSubs'=>CasterNtripSubscriptionResourceStock2::collection($subs));//	
		}
	   
    }
	
	
	
	public function getstockingtotals(Request $request){
		
		$business = $request->business;
		$dealer = $request->dealer;
	   	$company = $request->company;
	   	$subsarray = [];
	    $total = 0;
		$count = 0;
		$colors = ['#58c098','#4ea285','#448471','#3a675e','#304a4c','#2a3b3c','#283535'];
		// green #1ecc33  red - #ff6060 pink - ´#f34fa0 blue - #4e9cff yellow - #f9e80d
		$subtypes = CasterSubscription::get()->toArray();
		
		
		foreach($subtypes as $subtype){
			
		$subs = CasterNtripSubscriptionStock::
				where('type',$subtype['type'])
				->when($business > 0, function ($q) use($business) {
					return $q->where('business',$business);
				})	
				->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
				})
				->when($company > 0, function ($q) use($company) {
						return $q->where('company',$company);
				})
				->where('stock',1)	
				->get();	
			
			
			if(count($subs) > 0){	
		    $subsarray[$count]['label'] = $subtype['title'];
		    $subsarray[$count]['value'] = count($subs);
			$subsarray[$count]['color'] = $colors[$count];	
			$total = $total + count($subs);
			$count++;	
			}
		   	
			
	   };
		
		
		return array('total'=>$total,'data'=>$subsarray);
		
	}
   
  
    public function destroy($id)
    {
        $sub = CasterNtripSubscriptionStock::findorfail($id);
		if($sub->delete()){
			return new CasterNtripSubscriptionStockResource($sub);
		}
    }
	
}