<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CasterBusiness;
use App\CasterDealer;
use App\CasterCompany;
use App\Rover;
use App\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\CasterBusinessResource as CasterBusinessResource;
use App\Http\Resources\CasterDealerResourceBranding as CasterDealerResourceBranding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class CasterBusinessController extends Controller 
{

	
   public function index(Request $request)
    {
	   $id = $request->id;
	   $stext = $request->stext;
	   $users = CasterBusiness::
	   	when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
		->when($stext != '', function ($q) use($stext) {
				return $q->where('title','LIKE','%'.$stext.'%');
					})	
		->where('archive',0)	
		->get();
	   return array('CasterBusiness'=>CasterBusinessResource::collection($users));//
    }
	
	
	public function store(Request $request)
    {
		
        $user = $request->isMethod('put') ? CasterBusiness::findorfail($request->value) : new CasterBusiness;
		$user->id = $request->value;
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
		$user->mobile = $request->mobile;
		$user->website = $request->website;
		$user->save();
		return new CasterBusinessResource($user);
	}
	
	 public function addreseller(Request $request)
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
			$reseller = new CasterBusiness;
		} else $reseller  = CasterBusiness::where('id',$request->value)->first();
		 
		$reseller->id = $request->value;
		$reseller->title = $request->text;
		$reseller->email = $request->email;
		$reseller->contact = $request->contact;
		$reseller->address1 = $request->address1;
		$reseller->address2 = $request->address2;
		$reseller->address3 = $request->address3;
		$reseller->towncity = $request->towncity;
		$reseller->county = $request->county;
		$reseller->postcode = $request->postcode;
		$reseller->latitude = $lat;
		$reseller->longitude = $long; 
		$reseller->tel = $request->tel;
		$reseller->mobile = $request->mobile;
		$reseller->website = $request->website;
		$reseller->logintitle = $request->logintitle; 
		$reseller->logo = $request->logo;
		$reseller->background= $request->background;
		$reseller->save();
		 
		// Create new dealer user //
		 $userslist = [];
		 
		foreach ($users as $user){
			
			$setpassword = 0;	
			
		 	if($user['changepassword'] != ''){
				$password = $user['changepassword'];
				$setpassword = 1;
			}
			
			if(!isset($user['value'])){ // New user so create password
				$password = $user['password'];
				$setpassword = 1;
			}			
		
		 if(isset($user['value'])){$setuser = User::where('id',$user['value'])->first();} else $setuser = new User;
		 
		 $setuser->fname = $user['fname'];
		 $setuser->lname = $user['lname'];
		 $setuser->email = $user['email'];
		 if($setpassword == 1){	
		 	$setuser->password = Hash::make($password);
		 }	
		 $setuser->business = $reseller->id;
		 if(isset($user['readonly'])){$setuser->readonly = $user['readonly'];}	
		 $setuser->role = 5;
		 $setuser->save();
		
		 $userslist[] = $setuser->id;	
		}
		 
		 // Remove any deleted users
		 
		  $deluser = User::
		 	where('business',$reseller->id)
			->where('role',5)	
			->whereNotIn('id',$userslist)
			->update(['archive' => 1]);
		 
		return new CasterBusinessResource($reseller);
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
	    $reseller = CasterBusiness::where('id',$request->id)->first();
		$reseller->archive = 1;
		$reseller->save();
		 
		$dealers = CasterDealer::where('business',$request->id)->get();
		foreach($dealers as $dealer){
			$dealer->archive = 1;
			$dealer->save();
			}
		 
		$companies = CasterCompany::where('business',$request->id)->get();
			foreach($companies as $company){
			$company->archive = 1;
			$company->save();
			}
		 
		$rovers = Rover::where('business',$request->id)->get();	
			foreach($rovers as $rover){
			$rover->archive = 1;
			$rover->save();
			}
		 
		 $users = User::where('business',$request->id)->get();	
			foreach($users as $user){
			$user->archive = 1;
			$user->save();
			}
		 
		 
		return;
	 }

}
