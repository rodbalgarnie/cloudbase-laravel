<?php
// Version 251022/1400 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rover;
use App\CompanyMachine;
use App\CasterSimm;
use App\CasterSession;
use App\CasterNtripSubscription;
use App\RTKStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoverResource as RoverResource;
use App\Http\Resources\RoverResourceEdit as RoverResourceEdit;
use App\Http\Resources\RoverResourceUsers as RoverResourceUsers;
use App\Http\Resources\RoverResourceUsersShort as RoverResourceUsersShort;
use App\Http\Resources\RoverResourceMap as RoverResourceMap;
use App\Http\Resources\CasterSessionMapResource as SessionMapResource;
use DB;

class CasterRoversController extends Controller
{

	
   public function index(Request $request)
    {
	   $id = $request->id;
	   $username = $request->username;
	   $client = $request->client;
	   
	   $rovers = Rover::
	   		when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
			->when($username !== '', function ($q) use($username) {
					return $q->where('username',$username);
			})	
	   		->get();
	   
	   
	   if($request->short == 1){
		   return array('rovers'=>RoverResourceEdit::collection($rovers));
	   } else return array('rovers'=>RoverResource::collection($rovers));//
    }
	
		public function indexmap(Request $request)
    {
	   	$id = $request->id;
		$business = $request->business;	
		$dealer = $request->dealer;
	   	$company = $request->company;
		$single = $request->single;
		$logged = $request->logged;	
			
		if($single == 0){	
		
		$rovers = Rover::
				when($business > 0, function ($q) use($business) {
					return $q->where('business',$business);
				})
				->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
				})
				->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
				})
				->when($logged != 0, function ($q){
    			return $q->where('rtk_status','!=',0);
				})	
				->where('rtk_status','>',0)
				->orderby('title','ASC')	
				->get();
		
		} else {
			
			$rovers = Rover::where('id',$id)->get();
		}
			
		$chartdata  = $this->getloggedrovertotals($business,$dealer,$company);	
			
	   return array('rovers'=>RoverResourceMap::collection($rovers),'chartdata'=>$chartdata);///
    }
	
	public function getloggedrovertotals($business,$dealer,$company){
		
		$subsarray = [];
	    $status = 1;
		$total = 0;
		
		$states = RTKStatus::where('code','>',0)->get();

		foreach($states as $state){
			
		$rovers = Rover::
				when($business > 0, function ($q) use($business) {
					return $q->where('business',$business);
				})
				->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
				})
				->when($company > 0, function ($q) use($company) {
						return $q->where('company',$company);
				})
				->where('rtk_status',$state->code)
				->get();	
		
		 
		    $subsarray[$status]['label'] = $state->message;
		    $subsarray[$status]['value'] = count($rovers);
			$subsarray[$status]['color'] = $state->colour;
			
			if(count($rovers) > 0){$total = $total + count($rovers);}
		   	$status++;
	   };
		
		
		$subsarray[1]['total'] = $total;
		return array('total'=>$total,'data'=>$subsarray);
		
	}

	
	public function indexrovercompany(Request $request)
    {
		$id = $request->id;
		$reseller = $request->reseller;
		$dealer = $request->dealer;
	   	$company = $request->company;
		$stext = $request->stext;	
		$username = $request->username;
	   
	   $rovers = Rover::with('companys')
	   		->when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
		    ->when($username !== null, function ($q) use($username) {
					return $q->where('username',$username);
			})	
			->when($reseller > 0, function ($q) use($reseller) {
					return $q->where('business',$reseller);
			})		
			->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})	
	   		->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})
			->when($stext != '', function ($q) use($stext) {
				return $q->whereRelation('companys', 'title', 'like', '%'.$stext.'%');
					})
		   
		   ->where('archive',0)	
		 	->get();
		
		
		
	if($id > 0 || $username !== null){return array('rovers'=>RoverResourceUsers::collection($rovers));} else return array('rovers'=>RoverResourceUsersShort::collection($rovers));//	
	   
    }

	
    public function store(Request $request)
    {
		$simm = $request->ccidsel;
		$machineid = $request->machine;
		
        $rover = $request->isMethod('put') ? Rover::findorfail($request->value) : new Rover;
		$rover->id = $request->value;
		$rover->title = $request->text;
		$rover->business = $request->business;
		$rover->dealer = $request->dealer;
		$rover->company = $request->company;
		$rover->machine = $machineid;
		$rover->simm = $simm;
		$rover->username = $request->username;
		$rover->password = $request->password;
		
		$rover->save();
		
		
		$simmclear = CasterSimm::where('rover',$request->value)->first();
		if($simmclear){
			$simmclear->rover = 0;
			$simmclear->stock = 1;
			$simmclear->save();
		}	
		
		if($simm > 0){
		$simm = CasterSimm::where('id',$simm)->first();
		$simm->company = $request->company;
		$simm->rover = $request->value;	
		$simm->stock = 0;
		$simm->save();
		}
		
		
		// Clear current rover machine value	
		$machineclear = CompanyMachine::where('rover',$request->value)->first();
		if($machineclear){
			$machineclear->rover = 0;
			$machineclear->save();
		}
		
		if($machineid > 0){
		// Set new rover value		
		$machine = CompanyMachine::where('id',$machineid)->first();
		$machine->rover = $request->value;
		$machine->save();
		}
		
		return;
	}
	
	
	
    public function show($id)
    {
        $rover = Rover::findorfail($id);
		return $rover;
    }


    public function archive(Request $request)
    {
		$id = $request->id;
		$rover = Rover::where('id',$id)->first();
		$rover->archive = 1;
		$rover->save();
		$machine = CompanyMachine::where('rover',$id)->update(['rover'=>0]);
		$sub = CasterNtripSubscription::where('rover',$id)->update(['archive'=>1,'status'=>1]);
		
		return;
		
    }
	
	public function getroversubstotals(Request $request){
		
		$dealer = $request->dealer;
	   	$company = $request->company;
	   	$subsarray = [];
	    $status = 1;
		$total = 0;
		
		// 1 -Stock 2 - Pending 3 - Active 4 - Expired 5 - Suspended //
		$names = ['Active','Expired','Suspended'];
		$colors = ['rgba(60, 210, 165, 0.95)','#448471','#4ea285'];
		
		while($status !== 4){
			
		$rovers = Rover::with('subscriptions')
				->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
				})
				->when($company > 0, function ($q) use($company) {
						return $q->where('company',$company);
				})
				->get();
				
		
		 
		    $subsarray[$status]['label'] = $names[$status-1];
		    $subsarray[$status]['value'] = count($rovers);
			$subsarray[$status]['color'] = $colors[$status-1];
			
			if(count($rovers) > 0){$total = $total + count($rovers);}
		   	$status++;
	   };
		
		return array('total'=>$total,'data'=>$subsarray);
	}
	
	public function bm2mlogin(){
		
		$response = Http::post('https://www.commsportal.com/api/sign_in', [
			'username' => 'rod.balgarnie@nickabbey.co.uk',
			'password' => 'DigitalAg1!',
		]);
		
		if(isset($response['usertoken'])){
			return($response['usertoken']);
			} else return null;
		}
	
	public function bm2msetstatus($id,$action){
		
		$token = $this->bm2mlogin();
		if($token == null){return 'LOGIN ERROR';}
		
		
		$response = Http::post('https://www.commsportal.com/api/connections/'.$id.'/action', [
			'username' => 'rod.balgarnie@nickabbey.co.uk',
			'usertoken' => $token,
			'name' => $action
		]);
		
		return $response;
		
	}
}
