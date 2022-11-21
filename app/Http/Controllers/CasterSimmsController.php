<?php
// Version 091122
namespace App\Http\Controllers;

use Illuminate\Http\Request;  
use App\CasterSimm;
use App\CasterDealer;
use App\CasterCompany;
use App\CasterSimmStatus;
use App\CasterSimmPackage;
use App\CasterSimmPackageUsage;
use App\CasterSimmUsage; 
use App\CasterNtripSubscription;
use App\CasterNtripSubscriptionStock;
use App\CompanyMachine;
use App\Rover;
use App\System;
use App\CasterEvent; 
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\CasterSimmResource as CasterSimmResource;
use App\Http\Resources\RoverResource as RoverResource; 
use DB;

class CasterSimmsController extends Controller
{
	
	public function simmusage(Request $request){
		$dealer = $request->dealer;
		$company = $request->company;
		
		$rovers = Rover::select('id')
				->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
				})
				->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
				})
				->get();
		
		$simms = CasterSimm::with('simmusages')
			->whereIn('rover',$rovers)
			->where('status',1)
			->get();
		
		foreach($simms[0]['simmusages'] as $usage){
				$months[] = array('month'=>$usage['usage_date'],'total'=>0);	
			}
		
		
		
		
		$totals = [];
		
		foreach($simms as $simm){
			foreach($simm['simmusages'] as $usage){
				
				$date = $usage['usage_date'];
				
				$month = array_filter($months, function($var) use($date){
					return ($var['month'] == $date);
				});
			
				
				if(isset($month)){
				$month[0]['total'] = 999;//$month[0]['total'] + $usage['usage_mb'];
				$test[] = array($simm['iccid'],$date,$month);
				}
			
				}
		}
		
		return $test;
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
	

	public function bm2mgetpackage(){	// Update package totals 
		
			$currentdate = date('M y');
		
			$token = $this->bm2mlogin();
			if($token == null){return 'LOGIN ERROR';}
		
			$packages = CasterSimmPackage::get();
		
			foreach($packages as $pack){
				
			$package = $pack->package_id;
			$package_id = $pack->id;
		
			$response = Http::get('https://www.commsportal.com/api/packages/'.$package, [
				'username' => 'rod.balgarnie@nickabbey.co.uk',
				'usertoken' => $token,
				'per_page' => 500,
				'page_cons'=>0
			]);
				
			$connections = $response['connections'];
			
		
			foreach($connections as $connection){
				
				if($connection['status'] == 'active'){	

				$usagemb = (float)$connection['current_usage']['raw_usage']/1000000;

				$currentsimmmonthusage = CasterSimmUsage::
						where('connectionid',$connection['id'])
						->where('usage_date',$currentdate)	
						->update([
							'usage_raw' => $connection['current_usage']['raw_usage'],
							'usage_mb' => $usagemb
						]);

					if($currentsimmmonthusage == 0){ // new month so create new record
						$newmonth = new CasterSimmUsage;
						$newmonth->connectionid = $connection['id'];
						$newmonth->usage_date = $connection['current_usage']['usage_date'];
						$newmonth->usage_raw = $connection['current_usage']['raw_usage'];
						$newmonth->usage_mb = $usagemb;
						$newmonth->save();
					}

				}
				
			}
				
				
			$currentmonthusage = CasterSimmPackageUsage::
					where('package',$package_id)
					->where('usage_date',$currentdate)
					->first();
				
				
				
			if($currentmonthusage){
				// Update current package month totals
				$currentmonthusage->usage_raw = $response['usages'][0]['raw_usage'];
				$currentmonthusage->usage_gb = $response['usages'][0]['raw_usage']/1000000000;
				$currentmonthusage->save();
			} 
			
			else { // new month so create new record
				$newmonth = new CasterSimmPackageUsage;
				$newmonth->package = $package_id;
				$newmonth->usage_date = $response['usages'][0]['usage_date'];
				$newmonth->usage_raw = $response['usages'][0]['raw_usage'];
				$newmonth->usage_gb = $response['usages'][0]['raw_usage']/1000000000;
				$newmonth->save();
			}
		
			if($package_id == 1){$allowance = $response['allowance']/1000000000;}
				else $allowance = 12;
				
			$usagegb = (float)$response['usages'][0]['raw_usage']/1000000000;
			
			$update = CasterSimmPackage::where('package_id',$package)
				->update([
					'data_allowance' => $allowance,
					'data_used' => $usagegb,
					'data_usage' => round(($usagegb/$allowance) * 100),
					'data_usage_date' => $response['usages'][0]['usage_date']
				]);
				
			} // End packages loop
		
			return;
		
	}
	
	public function bm2mgetconnections(Request $request){
		
		$simmlist = json_decode($request->simms,true);
		
		
		$token = $this->bm2mlogin();
		if($token == null){return 'LOGIN ERROR';}
		
		if($simmlist == ''){
			$connections = CasterSimm::pluck('connectionid');
			} else $connections = $simmlist;
		
		
		foreach($connections as $conn){
			
			$cid = Http::get('https://www.commsportal.com/api/connections/'.$conn, [
				'username' => 'rod.balgarnie@nickabbey.co.uk',
				'usertoken' => $token,
			]);
			
			switch($cid['status']){
				case 'active':
				$status = 1;
				break;
				case 'inactive':
				$status = 2;
				break;	
				case 'suspended':
				$status = 3;
				break;
				default:
				$status = 0;
				break;	
					
			}
			
			$usages = $cid['usages'];
			$usage = $usages[count($usages)-1]['usage'];
			
			if(isset($cid['apn_name'])){
				if($cid['apn_name'] == 'E2 - dynamic'){$apn = 'wbdata';} else $apn = $cid['apn_name'];
			}
			
			$simm = CasterSimm::
				where('iccid',$cid['iccid'])
				->update([
					//'package_id' => $cid['feature_id'],
					'iccid' => $cid['iccid'],
					'supplier' => $cid['supplier_name'],
					'service' => $cid['service'],
					'statustext' => $cid['status'],
					'status' => $status,
					'online' => $cid['is_online'],
					'connectionid' => $cid['id'],
					'datausedmonth' => $usage,
					'apn'=>$apn
				]);
			
		
			
		}
			
		return $cid;
	}
	
	public function bm2mgetactivesimms(Request $request){
		
		$token = $this->bm2mlogin();
		if($token == null){return 'LOGIN ERROR';}
		$simms = [];
		
		$usedsimms = CasterSimm::pluck('connectionid')->toArray();
		
			$response = Http::get('https://www.commsportal.com/api/connections', [
				'username' => 'rod.balgarnie@nickabbey.co.uk',
				'usertoken' => $token,
			]);
		
		$connections = json_decode($response);
		
		foreach($connections as $connection){
			
			if($connection->status == 'active' && in_array($connection->id, $usedsimms) == false){
				$simms[] = array('id'=>$connection->id,'iccid'=>$connection->iccid,'value'=>false);
			}
		}
		return $simms;
		
	}
	
	public function bm2mgetinactivesimms(Request $request){
		
		$token = $this->bm2mlogin();
		if($token == null){return 'LOGIN ERROR';}
		$simms = [];
		
		$usedsimms = CasterSimm::pluck('connectionid')->toArray();
		
			$response = Http::get('https://www.commsportal.com/api/connections', [
				'username' => 'rod.balgarnie@nickabbey.co.uk',
				'usertoken' => $token,
			]);
		
		$connections = json_decode($response);
		
		foreach($connections as $connection){
			
			if($connection->status == 'inactive' && in_array($connection->id, $usedsimms) == false){
				$simms[] = array('id'=>$connection->id,'iccid'=>$connection->iccid,'value'=>false);
			}
		}
		return $simms;
		
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
	
   public function index(Request $request)
    {
	
	   $id = $request->id;
	   $dealer = $request->dealer;
	   $company = $request->company;
	   $rover = $request->rover;
	   $stock = $request->stock;		// 99 All 1 Stock 0 Not stock
	   $status = $request->status;
	   $stext = $request->stext;
	   
	   
	   $simms = CasterSimm::
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
		->when($stock != 99, function ($q) use($stock) {
					return $q->where('stock',$stock);
			})		
		->when($status > 0, function ($q) use($status) {
					return $q->where('status',$status);
			})	
		->when($stext != '', function ($q) use($stext) {
				return $q->where('iccid','LIKE','%'.$stext.'%');
					})	
		->get();
	   return array('CasterSimmss'=>CasterSimmResource::collection($simms));//
    }
	
	
	public function dealersimmtotals(Request $request){
		
		$status = $request->status;
		
		$dealers = CasterDealer::select('id','title')->where('archive',0)->orderBy('title','ASC')->get()->toArray();
		
		foreach($dealers as $dealer){
			
		$data = CasterSimm::
			where('dealer',$dealer['id'])
			->when($status > 0, function ($q) use($status) {
					return $q->where('status',$status);
			})
			->get();
	   		
			$count = count($data);
			
				$string = $dealer['title'].' ('.$count.')';
				$totals[] = array('value'=>$dealer['id'],'text'=>$string);
			
			}
		
			$data = CasterSimm::
			when($status > 0, function ($q) use($status) {
			return $q->where('status',$status);
			})
			->get();
	   		$count = count($data);
			array_unshift($totals,array('value'=>0,'text'=>'All ('.$count.')'));
			
			return $totals;
	}
	
	public function companysimmtotals(Request $request){
		
		$dealer = $request->dealer;
		$status = $request->status;
		$companies = CasterCompany::select('id','title')
			->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})
			->where('archive',0)
			->orderBy('title','asc')
			->get()->toArray();
		$totals = [];
		
		foreach($companies as $company){
			
		$data = CasterSimm::
			where('company',$company['id'])
			->when($status > 0, function ($q) use($status) {
					return $q->where('status',$status);
			})
			->get();
	   		
			$count = count($data);
			if($count > 0){
				$string = $company['title'].' ('.$count.')';
				$totals[] = array('value'=>$company['id'],'text'=>$string);
			}
			
			}
		
			$data = CasterSimm::
			where('company','>',0)
			->when($status > 0, function ($q) use($status) {
			return $q->where('status',$status);
			})
			->when($dealer > 0, function ($q) use($dealer) {
			return $q->where('dealer',$dealer);
			})	
			->get();
		
			$count = count($data);
		
			if(count($totals) > 0){
			array_unshift($totals,array('value'=>0,'text'=>'All ('.$count.')'));
			} else $totals[] = array('value'=>0,'text'=>'All ('.$count.')');
			return $totals;
	}
	
	public function statussimmtotals(Request $request){
		$dealer = $request->dealer;
		$company = $request->company;
		$status = $request->status;
		
		$statuss = CasterSimmStatus::select('id','text')->get()->toArray();
		$totals = [];
		
		
		foreach($statuss as $state){
			
		$data = CasterSimm::
			where('status',$state['id'])
			->when($dealer > 0, function ($q) use($dealer){
				return $q->where('dealer',$dealer);
			})	
			->when($company > 0, function ($q) use($company){
				return $q->where('company',$company);
			})
			->get();
	   		
			$count = count($data);
			if($count > 0){
				$string = $state['text'].' ('.$count.')';
				$totals[] = array('value'=>$state['id'],'text'=>$string);
			}
			
			}
		
			$data = CasterSimm::
			where('status','>',0)
			->when($dealer > 0, function ($q) use($dealer){
				return $q->where('dealer',$dealer);
			})	
			->when($company > 0, function ($q) use($company){
				return $q->where('company',$company);
			})
			->get();
		
			$count = count($data);
		
			if(count($totals) > 0){
			array_unshift($totals,array('value'=>0,'text'=>'All ('.$count.')'));
			} else $totals[] = array('value'=>0,'text'=>'All ('.$count.')');
			return $totals;
	}
	
	public function indextotals(Request $request)
    {
	   	$dealer  = $request->dealer;
		$company = $request->company;
		$status = $request->status;
		$totals = [];
		
	   	$simms = CasterSimmPackage::select('id','text')->get();
		
		
		foreach($simms as $simm){
			
			$data = CasterSimm::
	   		when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})
			->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})
			->when($status > 0, function ($q) use($status) {
					return $q->where('status',$status);
			})
			->where('package_id',1)
			->get();
			
			$count = count($data);
			if($count > 0){
				$string = $simm->text.' ('.$count.')';
				$totals[] = array('value'=>$simm->id,'text'=>$string);
			}
		}
		
		return $totals;
		
    }
	public function indexstock(Request $request)
    {
	   
	   	$dealer = $request->dealer;
		$rover = $request->rover;
		
		if($rover == 0){  // Get just available stock simms
	   
		$simms = CasterSimm::
	  		where('dealer',$dealer)
			->where('stock',1)
			->orderBy('iccid','ASC')
			->get();
	  	}
		
		else {	// Get current Rover SIM and  stock simms
			
			$simms1 = CasterSimm::
			where('rover',$rover)
			->get();
			
			$simms2 = CasterSimm::
				where('dealer',$dealer)
				->where('stock',1)
				->orderBy('iccid','ASC')
				->get();
			
			$simms = $simms1->merge($simms2); 
			
		}
		
	   	return array('CasterSimms'=>CasterSimmResource::collection($simms));	
    }

  
    public function updatesimm(Request $request)
    {
	
			switch($request->status){
				case 1:
				$status = 3; 
				$action = 'suspend';	
				break;		
				case 2:
				$status = 1; 
				$action = 'activate';	
				break;
				case 3:
				$status = 1; // unsuspend
				$action = 'unsuspend';	
				break;
				default:
				$status = 0;
				$response = 'dealer changed';	
				break;	
				}
	
		
        $simm = CasterSimm::findorfail($request->value);
		$oldstatus = $simm->status;
		$simm->id = $request->value;
		$simm->package_id = $request->package_id;
		$simm->dealer = $request->dealer;
		$simm->rover = $request->rover;
		$simm->iccid = $request->number;
		if($status != 0){$simm->status = $status;}
		$simm->save();
		
		// Update Betterconnect if status change
		if($status != 0){
			if($oldstatus !== $status){
				$response = $this->bm2msetstatus($simm->connectionid,$action);	
			} else $response = 'no change';
		}
//		
		return $response;
	}
	
	
	 public function updatesubscription(Request $request){
		 
	
		$subeventcode = 54;
		$currentsub = CasterNtripSubscription::where('id',$request->subid)->first();
		$rover = Rover::where('id',$request->rover)->first();
		$startdate = date('Y-m-d H:i:s',strtotime($request->startdate));
		$enddate= date('Y-m-d H:i:s',strtotime($request->enddate)); 
		
		if($request->action > 0){
			 
		 	 switch($request->action){
				 
				case 1:	 
					$actiontext = 'suspend';
					$status = 3;
					$active = 0;
					$substatus = 5;
					$statustext = 'suspended';
					$subeventcode = 51; 
					break;
				
				case 3: 
					$actiontext = 'unsuspend';
					$status = 1;
					$active = 1;
					$substatus = 3;  
					$statustext = 'active';
					$subeventcode = 52;  
					break;
					 
				case 4:
					$actiontext = 'cancel';
					$status = 3;
					$active = 0;
					$substatus = 6;
					$statustext = 'cancelled';
					$subeventcode = 53; 
					break; 		 
						 
			 	}
				
				if($request->nosimm == 0){
					$simm = CasterSimm::where('id',$request->simm)->first();
					$simm->status=$status;
					$simm->statustext=$statustext;
					$simm->save();

					// Update Betterconnect to update simm suspend/activate etc
					$response = $this->bm2msetstatus($simm->connectionid,$actiontext);
					}
		
					// Update Subscription
					$sub = CasterNtripSubscription::where('id',$request->subid)->first();
					$sub->active = $active;
					$sub->user = $request->user;
					$sub->status = $substatus;
					$sub->save();
			
					$result =  DB::connection('mysql2')->update('update subscriptions set Allowed = '.$active.' where Username = ?', array($rover->username));
			}
		 
		 	// Check for SIM changes
		 	
		 
		 	if($request->simm == 0 && $currentsub->nosimm == 0){ //	Change to no simm
				$currentsim = CasterSimm::where('rover',$request->rover)->first();
				$currentsim->rover = 0;
				$currentsim->company = 0;	
				$currentsim->save();
				$currentsub->nosimm = 1;
				$currentsub->save();
				$rover->simm = 0;
				$rover->save();
			}
		 
		 	if($request->simm > 0 && $currentsub->nosimm == 1){ //	Change to having simm
				$newsim = CasterSimm::where('id',$request->simm)->first();
				$newsim->rover = $request->rover;
				$newsim->company = $request->company;	
				$newsim->save();
				$currentsub->nosimm = 0;
				$currentsub->save();
				$rover->simm = $request->simm;
				$rover->save();
				}
		 
		 	if($request->simm > 0){
				$currentsim = CasterSimm::where('rover',$request->rover)->first();  //	Change of just sim number
				if($request->simm !== $currentsim->id){
					$currentsim->rover = 0;
					$currentsim->company = 0;	
					$currentsim->save();
					$newsim = CasterSimm::where('id',$request->simm)->first();
					$newsim->rover = $request->rover;
					$newsim->company = $request->company;	
					$newsim->save();	
					$rover->simm = $request->simm;
					$rover->save();	
				}
			}
		 
		 
		 	
		 	// Update subscription username password if changed
		 	if($rover->username !== $request->username || $rover->password !== $request->password){
				
				$result =  DB::connection('mysql2')->update("update subscriptions set Username = '$request->username' where Username = ?", array($rover->username));
				$result =  DB::connection('mysql2')->update("update subscriptions set Password = '$request->password' where Username = ?", array($request->username));
				
				$rover->username = $request->username;
				$rover->title = $request->username;
				$rover->password = $request->password;
				$rover->save();
			}
		 
		 	// Update connection and port details if changed
		 	if($rover->port !== $request->port || $rover->connection !== $request->connection){
				$rover->connection = $request->connection;
				$rover->port = $request->port;
				$rover->save();
			}
		 
		 
		 	 // Update Caster if expiry date changed	
		 	if($currentsub->enddate !== $enddate){
				$result =  DB::connection('mysql2')->update("update subscriptions set Expiry_date = '$enddate' where Username = ?", array($rover->username));
				$currentsub->enddate = $enddate;
				$currentsub->save();	
				}
		 
		 	 // Update if start date changed	
		 	if($currentsub->startdate !== $startdate){
				$currentsub->startdate = $startdate;
				$currentsub->save();	
			}
		 
		 	 // Check if PO changed	
		 	if($currentsub->purchase_order !== $request->purchase_order){
				$currentsub->purchase_order = $request->purchase_order;
				$currentsub->save();	
			}
		 
		 	// Check for subscription type change 
		 	$test = '';
		 	if($currentsub->stocksub !== $request->newsub){
			
				// Put OLD one back in stock
				$stocksub = CasterNtripSubscriptionStock::where('id',$currentsub->stocksub)->first();
				$stocksub->stock = 1;
				$stocksub->company = 0;
				$stocksub->rover = 0;
				$stocksub->startdate = null;
				$stocksub->enddate = null;
				$stocksub->status = 1;
				$stocksub->sub_id = 0;
				$stocksub->save();
				
				// Mark NEW Stock sub
				
				$stocksub = CasterNtripSubscriptionStock::where('id',$request->newsub)->first();
				$stocksub->stock = 0;
				$stocksub->company = $request->company;
				$stocksub->rover = $request->rover;
				$stocksub->startdate = date('Y-m-d H:i:s',strtotime($request->startdate));
				$stocksub->enddate = date('Y-m-d H:i:s',strtotime($request->enddate));
				$stocksub->status = 3;
				$stocksub->sub_id = $currentsub->id;
				$stocksub->save();
				
				// Update Subcription record
				
				$currentsub->type = $request->type;
				$currentsub->startdate = date('Y-m-d H:i:s',strtotime($request->startdate));
				$currentsub->enddate = date('Y-m-d H:i:s',strtotime($request->enddate));
				$currentsub->stocksub = $request->newsub;
				$currentsub->save();
				
				// Update Casta with new end dates
				$result =  DB::connection('mysql2')->update("update subscriptions set Expiry_date = '$enddate' where Username = ?", array($rover->username));
				
			}
		 
		 	
		 
		 	// If renewal earmark a stock subscription for the renewal date
		 	if($currentsub->renew_once == 0 && isset($request->renewtype)){	
				$newsub = CasterNtripSubscriptionStock::
					where('dealer',$request->dealer)
					->where('type',$request->renewtype)
					->first();
				
				$newsub->sub_id = $request->subid;
				$newsub->company = $request->company;
				$newsub->rover = $request->rover;
				$newsub->stock = 0;
				$newsub->status = 2; // Pending
				$newsub->purchase_order = $request->renewref;		
				$newsub->startdate = date('Y-m-d H:i:s',strtotime($request->renewstartdate));
				$newsub->enddate = date('Y-m-d H:i:s',strtotime($request->renewenddate));
				$newsub->save();
				
				$currentsub->renew_once = 1;
		 		$currentsub->save();
				
			}
		 
		 // Check for renew omce change from yes to no
		 if($currentsub->renew_once == 1 && $request->renew_once == 0){	
			 
			 // Find ear marked sub and put back in stock
			 $newsub = CasterNtripSubscriptionStock::
					where('rover',$request->rover)
					->where('status',2)
					->first();
			 
			 	$newsub->sub_id = 0;
				$newsub->company = 0;
				$newsub->rover = 0;
				$newsub->stock = 1;
				$newsub->status = 1; 
				$newsub->purchase_order = '';		
				$newsub->startdate = null;
				$newsub->enddate = null;
				$newsub->save();
			 
			 	$currentsub->renew_once = 0;
			 	$currentsub->renewsent = 0;
			 	$currentsub->save();
		 }
		 
		
		 
		 // Check renewal sub change
		 $renewsub = CasterNtripSubscriptionStock::
					where('rover',$request->rover)
					->where('status',2)
					->first();
		 
		 if($renewsub !== null){
			 
		 if($renewsub->purchase_order !== $request->renewref){
			 $renewsub->purchase_order = $request->renewref;
			 $renewsub->save();
		 }
			 
		 if($renewsub->type !== $request->renewtype){
			 
			 	// Mark NEW Stock sub
			 	$newsub = CasterNtripSubscriptionStock::
			 		where('id',$request->renewsubid)
					->first();	
				
			 	$newsub->sub_id = $request->subid;
			 	$newsub->company = $request->company;
				$newsub->rover = $request->rover;
				$newsub->stock = 0;
				$newsub->status = 2; // Pending
				$newsub->purchase_order = $request->renewref;		
				$newsub->startdate = date('Y-m-d H:i:s',strtotime($request->renewstartdate));
				$newsub->enddate = date('Y-m-d H:i:s',strtotime($request->renewenddate));
				$newsub->save();
//				
//				
//				// Put OLD one back in stock
				$renewsub->stock = 1;
				$renewsub->company = 0;
				$renewsub->rover = 0;
			 	$renewsub->purchase_order = '';
				$renewsub->startdate = null;
				$renewsub->enddate = null;
				$renewsub->status = 1;
				$renewsub->sub_id = 0;
				$renewsub->save();
//				
		 	}
			 
			// Activate an expired subscription - ADDED 26/10/22
			 
			 if($renewsub->status == 2 && $request->status == 4){ //&& (strtotime($renewsub->startdate) < strtotime('now'))){
				 
				 $renewsub->status = 3;	//	Switch new sub from pending to active.
				 $renewsub->save();
				 
				 $currentsub->thirtyday = 0;	// Reset live subscription
				 $currentsub->sevenday = 0;
				 $currentsub->oneday = 0;
				 $currentsub->renewsent = 0;
				 $currentsub->stocksub = $request->renewsubid;
				 $currentsub->startdate = $renewsub->startdate;
				 $currentsub->enddate = $renewsub->enddate;
				 $currentsub->renew_once = 0;
				 $currentsub->status = 3;
				 $currentsub->active = 1;
				 $currentsub->save();
				 
				 $result =  DB::connection('mysql2')->update("update subscriptions set Expiry_date = '$renewsub->enddate' where Username = ?", array($request->username));

 			 } 
			
				
		}
		 
		// Create System event
		$rover = Rover::where('id',$request->rover)->first(); 
		$event = $this->createevent($request->user,'user',$subeventcode,$rover->username,$rover->id);
		 
		return ;
	}
	
	 public function roveradd(Request $request)
    {
		 
		$username = $request->username;
		$password = $request->password;	
		
        $rover = new Rover;
		$rover->title = $request->username;
		$rover->business = $request->resellerid; 
		$rover->dealer = $request->dealerid;
		$rover->company = $request->companyid;
		$rover->machine = $request->machineid;
		$rover->simm = $request->simm;
		$rover->connection = $request->connection;
		$rover->port = $request->port;  
		$rover->username = $username;
		$rover->password = $password;
		$rover->save();
		 
		 // Create System event
		$event = $this->createevent($request->user,'user',15,$rover->username,$rover->id); 
		 
		 
		 // Update Subscription  status PENDING or ACTIVE // 
		$start = date('Y-m-d H:i:s',strtotime($request->substart));
		$end = date('Y-m-d H:i:s',strtotime($request->subend));
		$now = date('Y-m-d H:i:s');
		 
		if(strtotime($request->start) < strtotime($now)){
			$active = 1;	// Active
			$status = 3;
		} else {
			$active = 0;
		 	$status = 2;	//	Pending
			$statustext = 'suspended';
		}
		 
		// Update Stock subscription 
  		$stocksub = CasterNtripSubscriptionStock::where('id',$request->subid)->first();
		$stocksub->company = $request->companyid;
		$stocksub->rover = $rover->id;
		$stocksub->stock = 0;
		$stocksub->status = 3; 
		$stocksub->purchase_order = $request->purchase_order; 
		$stocksub->user = $request->user;
		$stocksub->startdate = $start;
		$stocksub->enddate = $end;
		$stocksub->save();
		 
		 
		// Check if subscription for Rover
		$sub = CasterNtripSubscription::where('rover',$rover->id)->first();
		if($sub == null){$sub = new CasterNtripSubscription;} 
		$sub->business = $request->resellerid; 
		$sub->dealer = $request->dealerid; 
		$sub->company = $request->companyid;
		$sub->rover = $rover->id;
		$sub->type = $request->subtype;
		$sub->status = $status;
		$sub->stocksub = $stocksub->id; 
		$sub->nosimm = $request->nosimm;  
		$sub->user = $request->user;
		$sub->purchase_order = $request->purchase_order;  
		$sub->active = $active;
		$sub->autorenew = $request->autorenew;
		$sub->renew_once = $request->renew_once; 
		$sub->startdate = $start;
		$sub->enddate = $end;
		
		$firstactivation = $sub->first_activation;  
		if($firstactivation == null){ 
			$sub->first_activation = $start;
		}
		$sub->save(); 
		 
		$stocksub->sub_id = $sub->id;
		$stocksub->save(); 
		 
		$rover->subscription = $sub->id;
		$rover->save(); 
		 
		 // Update Client Machine with new rover id
		 
		$machine = CompanyMachine::where('id',$request->machineid)->first();
		$machine->rover = $rover->id;
		$machine->dealer = $request->dealerid; 
		$machine->save();
		 
		 // Update SIMM 
		 if($request->simm !== 0){
			 $simm = CasterSimm::where('id',$request->simm)->first();
			 $currentsimmstatus = $simm->status;
			 $simm->rover = $rover->id;
			 $simm->company = $request->companyid;
			 $simm->status = 1;
			 $simm->statustext = 'active';
			 $simm->stock = 0;
			 $simm->save();
		 }
		 
		 // Update CASTER with new subscription
		 
		$result =  DB::connection('mysql2')->
				 insert('insert into subscriptions (Username,Password,Expiry_date,Allowed) values (?, ?, ?, ?)', array($username,$password,$end, 1));
		 
		 // Update Betterconnect 
		if($request->simm !== 0){ 
			if($currentsimmstatus == 2){$action = 'activate'; }//	Simm not activated
			if($currentsimmstatus == 3){$action = 'unsuspend'; }//	Simm suspended
			if(isset($action)){$response = $this->bm2msetstatus($simm->connectionid,$action);}	
		}
		 
		$newrover = Rover::with('companys')->where('id',$rover->id)->first(); 
		return array('roverid'=>$newrover->id,'subid'=>$newrover->subscription,'useremail'=>$newrover->companys->email);
		 
	 }
	
	
	public function addsimms(Request $request){
		$simms = $request->simms;
		
		foreach($simms as $simm){
			$add = new CasterSimm;
			$add->dealer = $request->dealer;
			$add->iccid = $simm['iccid'];
			$add->connectionid = $simm['id'];
			$add->stock = 1;
			$add->save();
		}
		return $simms;
	}

   
    public function show($id)
    {
        $simm = CasterSimm::findorfail($id);
		return new CasterSimmResource($simm);
    }


    public function destroy($id)
    {
        $simm = CasterSimm::findorfail($id);
		if($simm->delete()){
			return new CasterSimmResource($simm);
		}
    }
	
	public function createevent($userid,$group,$type,$text,$roverid){
		
		$rover = Rover::where('id',$roverid)->first();
		$event = new CasterEvent;
		$event->eventgroup = $group;
		$event->type = $type;
		$event->user = $userid;
		$event->reseller = $rover->business;
		$event->dealer = $rover->dealer;
		$event->company = $rover->company;
		$event->rover = $roverid;
		$event->text = $text;
		$event->save();
	}
}
