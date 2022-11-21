<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\CasterCompany;
use App\CasterDealer;
use App\CasterBusiness;
use App\User;
use App\Rover;
use App\CasterNtripSubscription;
use App\Http\Controllers\Controller;
use App\Http\Resources\CasterDealerResource as CasterDealerResource;
use App\Http\Resources\CasterDealerResourceList as CasterDealerResourceList;
use App\Http\Resources\CasterDealerResourceFull as CasterDealerResourceFull;
use App\Http\Resources\CasterDealerResourceBranding as CasterDealerResourceBranding;
use App\Http\Resources\CasterResellerResourceBranding as CasterResellerResourceBranding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class CasterDealersController extends Controller
{
	
	public function casterloginlink(Request $request){
		$email = $request->email;
		$user = User::where('email',$email)->first();
		//return $user;
		
		if($user->dealer !== 0){
			$record = CasterDealer::where('id',$user->dealer);
		} else $record = CasterBusiness::where('id',$user->business)->first();
		
		$link = 'https://ip-rtk-aws.com/login/'.$record->logintitle;
		return $link;
	}

	
   public function index(Request $request)
    {
	   $id = $request->id;
	   $stext = $request->stext;
	   $bus = $request->business;
	   
	   
	   $users = CasterDealer::
	   	when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
		->when($stext != '', function ($q) use($stext) {
				return $q->where('title','LIKE','%'.$stext.'%');
					})
		->when($bus != 0, function ($q) use($bus) {
				return $q->where('business',$bus);
					})	
		->where('id','>',1)
		->where('archive',0)	
		->get();
	   
	   if($request->list == 0){
	   		return array('CasterDealers'=>CasterDealerResource::collection($users));
	   } else return array('CasterDealers'=>CasterDealerResourceList::collection($users));
    }
	
	public function indextotals(Request $request)
    {
	   	$company  = $request->company;
		$status = $request->status;
		$expired = $request->expired;
		$type = $request->type;
		
		if($expired == 0){$status2 = 1;$status = 0; } else $status2 = 0;
		
		$stock = 0;
		$totals = [];
		
	   	$dealers = CasterDealer::select('id','title')->get();
		
		foreach($dealers as $dealer){
			
			$subs = CasterNtripSubscription::
	   		when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})
			->when($status2 > 0, function ($q) {
					return $q->where('status','!=',4);
			})		
			->when($status > 0, function ($q) use($status) {
					return $q->where('status',$status);
			})
			->when($type > 0, function ($q) use($type) {
					return $q->where('type',$type);
			})		
			->where('dealer',$dealer->id)	
			->where('stock',$stock)
			->get();
			
			
			$count = count($subs);
			
				$string = $dealer->title.' ('.$count.')';
				$totals[] = array('value'=>$dealer->id,'text'=>$string);
			
		}
		
		return $totals;
		
    }
	
	public function getdealer(Request $request)
    {
	   $id = $request->id;
	   $dealer = CasterDealer::
	   		where('id',$id)
			->get();
	   return array('CasterDealers'=>CasterDealerResourceFull::collection($dealer));//
    }
	
	public function getdealerbranding(Request $request)
    {
		
	   $reseller = CasterBusiness::
	   		where('logintitle',$request->name)
			->get();
		
		if(count($reseller) == 0){
		
	   $dealer = CasterDealer::
	   		where('logintitle',$request->name)
			->get();
	   		return array('Role'=>10,'CasterDealers'=>CasterDealerResourceBranding::collection($dealer));
		} 
		
		else {
			
			if($reseller[0]->id == 1){	// system admin cloudbase
				$role = 1;
			} else $role = 5;
			return array('Role'=>$role,'CasterDealers'=>CasterResellerResourceBranding::collection($reseller));
    
			}	
		}
	
	 public function adddealer(Request $request)
    {
		$users = $request->users;
		 
		$postcode = $request->postcode;
		$latlong = $this->postcodelookup($postcode);
		$lat = null;
		$long = null;
		
		if($latlong['error'] == 0){
			$lat = $latlong['lat'];
			$long = $latlong['lat'];
			} 
		
		if($request->value == 0){ 
			$dealer = new CasterDealer;
		} else {
			
		$dealer = CasterDealer::where('id',$request->value)->first();
			
			
		}
			
		 
		$dealer->business = $request->business;
		$dealer->title = $request->text;
		$dealer->email = $request->email;
		$dealer->contact = $request->contact;
		$dealer->address1 = $request->address1;
		$dealer->address2 = $request->address2;
		$dealer->address3 = $request->address3;
		$dealer->towncity = $request->towncity;
		$dealer->county = $request->county;
		$dealer->postcode = $request->postcode;
		$dealer->tel = $request->tel;
		$dealer->latitude = $lat;
		$dealer->longitude = $long;
		$dealer->mobile = $request->mobile;
		$dealer->website = $request->website;
		$dealer->logintitle = $request->logintitle;
		$dealer->background= $request->background;
		$dealer->logo = $request->logo; 
		$dealer->save();
		 
		// Create new dealer user //
		 $userslist = [];
		 
		foreach ($users as $user){
		
		 $setpassword = 0;
		 if(isset($user['value'])){$setuser = User::where('id',$user['value'])->first();} else $setuser = new User;
			
		 if($user['changepassword'] != ''){
			$password = $user['changepassword'];
			$setpassword = 1;
		}
			
		if(!isset($user['value'])){ // New user so create password
			$password = $user['password'];
			$setpassword = 1;
		}		
		 
		 $setuser->fname = $user['fname'];
		 $setuser->lname = $user['lname'];
		 $setuser->email = $user['email'];
		 if($setpassword == 1){	
		 $setuser->password = Hash::make($password);
		 }
		 $setuser->business = $dealer->business;
		 $setuser->dealer = $dealer->id;
		 if(isset($user['readonly'])){$setuser->readonly = $user['readonly'];}	
		 $setuser->role = 10;
		 $setuser->save();
		
		 $userslist[] = $setuser->id;	
		}
		 
		 // Remove any deleted users
		 
		 $deluser = User::
		 	where('dealer',$dealer->id)
			->where('role',10)		
			->whereNotIn('id',$userslist)
			->update(['archive' => 1]);
		
		return new CasterDealerResource($dealer);
	}
	
	
    public function store(Request $request)
    {
		
		
		$postcode = $request->postcode;
		$latlong = $this->postcodelookup($postcode);
		$lat = null;
		$long = null;
		
		if($latlong['error'] == 0){
			$lat = $latlong['lat'];
			$long = $latlong['lat'];
			} 
		
		
		$user = $request->isMethod('put') ? CasterDealer::findorfail($request->value) : new CasterDealer;
		$user->id = $request->value;
		$user->business = $request->business;
		$user->title = $request->text;
		$user->email = $request->email;
		$user->contact = $request->contact;
		$user->address1 = $request->address1;
		$user->address2 = $request->address2;
		$user->address3 = $request->address3;
		$user->towncity = $request->towncity;
		$user->county = $request->county;
		$user->postcode = $request->postcode;
		$user->tel = $request->tel;
		$user->latitude = $lat;
		$user->longitude = $long;
		$user->mobile = $request->mobile;
		$user->website = $request->website;
		$user->save();
		return new CasterDealerResource($user);
	}
	

	
	public function postcodelookup($postcode){
		
		$error = 0;
		$lat = 0;
		$long = 0;
		$response = Http::get('https://api.postcodes.io/postcodes/'.$postcode, [
		]);
		if(isset($response['result'])){
			$lat = $response['result']['latitude'];
			$long = $response['result']['longitude'];
			} else $error = 1;
		
		return array('lat'=>$lat,'long'=>$long,'error'=>$error);
			
	}
	
	 public function archive(Request $request)
    {
		$id = $request->id; 
	    $dealer = CasterDealer::where('id',$id)->first();
		$dealer->archive = 1;
		$dealer->save();
		 
		$company = CasterCompany::where('dealer',$id)->update(['archive'=>1]);
		$rover = Rover::where('dealer',$id)->update(['archive'=>1]); 
		$sub = CasterNtripSubscription::where('dealer',$id)->update(['archive'=>1,'status'=>1]);
		$user = User::where('dealer',$id)->update(['archive'=>1]); 
		 
		 
		return;
	 }


}
