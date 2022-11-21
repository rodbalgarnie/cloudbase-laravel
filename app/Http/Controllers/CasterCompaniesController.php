<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CasterCompany;
use App\User;
use App\Rover;
use App\CompanyMachine;
use App\CasterNtripSubscription;
use App\Http\Controllers\Controller;
use App\Http\Resources\CasterCompanyResource as CasterCompanyResource;
use App\Http\Resources\CasterCompanyResourceShort as CasterCompanyResourceShort;
use App\Http\Resources\CasterCompanyResourceFull as CasterCompanyResourceFull;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class CasterCompaniesController extends Controller
{
	
   public function index(Request $request)
    {
	   $id = $request->id;
	   $stext = $request->stext;
	   $business = $request->business;
	   $dealer = $request->dealer;
	   
	   $users = CasterCompany::
	   	when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
		->when($business > 0, function ($q) use($business) {
					return $q->where('business',$business);
			})		
		->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})	
		->when($stext != '', function ($q) use($stext) {
				return $q->where('title','LIKE','%'.$stext.'%');
					})
		->where('archive',0)
		->orderBy('title','ASC')	
		->get();
	   
	   if($request->list){
		   return array('CasterCompanies'=>CasterCompanyResourceShort::collection($users));
	   } else return array('CasterCompanies'=>CasterCompanyResource::collection($users));
	   
    }
	
	public function indextotals(Request $request)
    {
	   	$dealer  = $request->dealer;
		$status = $request->status;
		$expired = $request->expired;
		if($expired == 0){$status2 = 1;$status = 0; } else $status2 = 0;
		$type = $request->type;
		$stock = 0;
		$totals = [];
		
	   	$companies = CasterCompany::select('id','title')->get();
		
		
		foreach($companies as $company){
			
			$subs = CasterNtripSubscription::
	   		when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
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
			->where('company',$company->id)	
			->where('stock',$stock)
			->get();
			
			$count = count($subs);
			if($count > 0){
				$string = $company->title.' ('.$count.')';
				$totals[] = array('value'=>$company->id,'text'=>$string);
			}
		}
		
		return $totals;
		
    }
	
	
	
	public function getcompany(Request $request)
    {
	   $id = $request->id;
	   $company = CasterCompany::
	   		where('id',$id)
			->get();
	  return array('CasterCompanies'=>CasterCompanyResourceFull::collection($company));//
    }


	 public function addcompany(Request $request)
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
			$company = new CasterCompany;
		} else {
			
		$company = CasterCompany::where('id',$request->value)->first();
			
		if($company->business !== $request->business || $company->dealer !== $request->dealer){
				
				
				$updaterovers = Rover::
					where('company',$company->id)
					->update(['business' => $request->business,'dealer' => $request->dealer]);

				$updateusers = User::
					where('company',$company->id)
					->update(['business' => $request->business,'dealer' => $request->dealer]);
			
				$updatesubs = CasterNtripSubscription::
					where('company',$company->id)
					->update(['business' => $request->business,'dealer' => $request->dealer]);
					}	
			
		}
		 
		$company->business = $request->business;
		$company->dealer = $request->dealer;
		$company->title = $request->text;
		$company->email = $request->email;
		$company->contact = $request->contact;
		$company->address1 = $request->address1;
		$company->address2 = $request->address2;
		$company->address3 = $request->address3;
		$company->towncity = $request->towncity;
		$company->county = $request->county;
		$company->postcode = $request->postcode;
		$company->tel = $request->tel;
		$company->latitude = $lat;
		$company->longitude = $long;
		$company->mobile = $request->mobile;
		$company->website = $request->website;
		$company->logo = $request->logo; 
		$company->logintitle = $request->logintitle; 
		$company->account = $request->account; 
		$company->save();
		 
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
		 $setuser->business = $request->business;
		 $setuser->dealer = $request->dealer;
		 $setuser->company = $company->id;	
		 if(isset($user['readonly'])){$setuser->readonly = $user['readonly'];}	
		 $setuser->role = 20;
		 $setuser->save();
		
		 $userslist[] = $setuser->id;	
		}
		 
		 // Remove any deleted company admin users
		 
		  $deluser = User::
		 	where('company',$company->id)
			->where('role',20)	
			->whereNotIn('id',$userslist)
			->update(['archive' => 1]);
		 
		return new CasterCompanyResource($company);
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
		
        $user = $request->isMethod('put') ? CasterCompany::findorfail($request->value) : new CasterCompany;
		$user->id = $request->value;
		$user->title = $request->text;
		$user->business = $request->business;
		$user->dealer = $request->dealer;
		$user->email = $request->email;
		$user->contact = $request->contact;
		$user->address1 = $request->address1;
		$user->address2 = $request->address2;
		$user->address3 = $request->address3;
		$user->towncity = $request->towncity;
		$user->county = $request->county;
		$user->postcode = $postcode;
		$user->latitude = $lat;
		$user->longitude = $long;
		$user->tel = $request->tel;
		$user->mobile = $request->mobile;
		$user->website = $request->website;
		$company->account = $request->account; 
		$user->save();
		return $user;// new CasterCompanyResource($user);
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
	    $company = CasterCompany::where('id',$request->id)->first();
		$company->archive = 1;
		$company->save();
		 
		$updaterovers = Rover::
				where('company',$company->id)
				->update(['archive' => 1]);

				$updateusers = User::
					where('company',$company->id)
					->update(['archive' => 1]);
			
				$updatesubs = CasterNtripSubscription::
					where('company',$company->id)
					->update(['archive' => 1,'active' => 0]);
		 
		 		$updatemachines = CompanyMachine::
					where('company',$company->id)
					->update(['archive' => 1]);
		return;
	 }
	

    public function destroy($id)
    {
        $user = CasterCompany::findorfail($id);
		if($user->delete()){
			return new CasterCompanyResource($user);
		}
    }
}
