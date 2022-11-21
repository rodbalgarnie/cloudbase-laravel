<?php
//Version 011022/1800
namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\CasterRequest;
use App\CasterRequestRTCM3;
use App\CasterRequestSatellite;
use App\CasterLog;
use App\CasterSession;
use App\CasterSimm;
use App\RTKStatus;
use App\Rover;
use App\BaseStation;
use App\CasterMessage;
use App\System;
use App\CasterStat;
use DB;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Http\Resources\CasterRequestResource as CasterRequestResource;
use App\Http\Resources\CasterRequestRTCM3Resource as CasterRequestRTCM3Resource;
use App\Http\Resources\CasterRequestSatelliteResource as CasterRequestSatelliteResource;
use App\Http\Resources\CasterLogResource as CasterLogResource;
use App\Http\Resources\RoverResource as RoverResource;
use App\Http\Resources\RoverResourceShort as RoverResourceShort;
use App\Http\Resources\RoverResourceFull as RoverResourceFull;
use App\Http\Resources\RoverResourceMap as RoverResourceMap;
use App\Http\Resources\CasterMessageResource as CasterMessageResource;
use App\Http\Resources\CasterSessionResource as CasterSessionResource;
use App\Http\Resources\CasterSessionShortResource as CasterSessionShortResource;
use App\Http\Resources\CasterSessionLongResource as CasterSessionLongResource;

	
class CasterRequestsController extends Controller
{
	public function disconnectclient(Request $request){
		
		
		$link = 'http://charlie:1234@nickabbeyservices.co.uk:2101/admin?mode=kick&argument=49';
		//$link = 'http://charlie:1234@nickabbeyservices.co.uk:2101/admin?mode=rehash';
		
		$response = Http::withHeaders([			//	Get data per device from Flespi
    				'accept' => 'application/json',
					//'Authorization' => 'FlespiToken XyeV5xS72tgx3e5XjuDeS9evvxKWJdZxz1guoXregvA7xvUGNmJvlLOE8pqHqf2P'
					])
  					->get($link);
		
		return $response;
//		$markers = MarkerFlespi::where('id','>',0)->delete();
//		$metrics = Metric::where('id','>',0)->delete();
//		$log = MarkerFlespiLog::where('id','>',0)->delete();
//		$last = MarkerLast::where('id','>',0)->delete();
//		$queue = RequestQueue::where('id','>',0)->delete();
		
		return;
	
	}
	
	

   public function index(Request $request)
    {
	   $bs = $request->basestation;
	   $rover = $request->rover;
	   $start = strtotime(str_replace('/','-',$request->start));
	   $end = strtotime(str_replace('/','-',$request->end));
	   
       $requests = CasterRequest::
	   		when($bs != 0, function ($q) use ($bs){
    			return $q->where('basestationid',$bs);
			})
			->when($rover != 0, function ($q) use ($rover){
    			return $q->where('rover_id',$rover);
			})
			->when($start != false, function ($q) use ($start){
    			return $q->where('timestamp','>=',$start);
			})	
			->when($end != false, function ($q) use ($end){
    			return $q->where('timestamp','<=',$end);
			})		
	   		->orderBy('timedate','DESC')
			->limit(500)	
	   		->get();
	   
	   return array('Requests'=>CasterRequestResource::collection($requests));//
    }
	
	public function indexRTCM3(Request $request)
    {
	   	$id = $request->id;
		$start = strtotime(str_replace('/','-',$request->start));
		$end = strtotime(str_replace('/','-',$request->end));
		
	   	$requests = CasterRequestRTCM3::
	   		when($id != 0, function ($q) use ($id){
    			return $q->where('basestation_id',$id);
			})
			->when($start != false, function ($q) use ($start){
    			return $q->where('rt_timestamp','>=',$start);
			})	
			->when($end != false, function ($q) use ($end){
    			return $q->where('timestamp','<=',$end);
			})		
	   		->orderBy('timestamp','DESC')
			->limit(500)	
	   		->get();
	   
	   return array('Requests'=>CasterRequestRTCM3Resource::collection($requests));//
    }
	
	public function indexsats(Request $request)
    {
	   	$id = $request->id;
		$basestation = $request->basestation;
		$start = strtotime(str_replace('/','-',$request->start));
		$end = strtotime(str_replace('/','-',$request->end));	
		
	   $requests = CasterRequestSatellite::
			when($id != 0, function ($q) use ($id){
    			return $q->where('id',$id);
			})
			->when($basestation != 0, function ($q) use ($basestation){
    			return $q->where('basestation_id',$basestation);
			})	
			->when($start != false, function ($q) use ($start){
    			return $q->where('timestamp','>=',$start);
			})	
			->when($end != false, function ($q) use ($end){
    			return $q->where('timestamp','<=',$end);
			})		
	   		->orderBy('timestamp','DESC')
			->limit(500)		
	   		->get();
	   
	   return array('sats'=>CasterRequestSatelliteResource::collection($requests));//
    }
	
	public function indexlog(Request $request)
    {
	   	$id = $request->id;
		$rover = $request->rover;
		$basestation = $request->basestation;
		$roversonly = $request->roversonly;
		$start = strtotime(str_replace('/','-',$request->start));
		$end = strtotime(str_replace('/','-',$request->end));
		
	   	$requests = CasterLog::
	   		when($id != 0, function ($q) use ($id){
    			return $q->where('error_code',$id);
			})
			->when($rover != 0, function ($q) use ($rover){
    			return $q->where('rover',$rover);
			})	
			->when($basestation != 0, function ($q) use ($basestation){
    			return $q->where('basestation',$basestation);
			})		
			->when($start != false, function ($q) use ($start){
    			return $q->where('timestamp','>=',$start);
			})	
			->when($end != false, function ($q) use ($end){
    			return $q->where('timestamp','<=',$end);
			})
			->when($roversonly == 1, function ($q){
    			return $q->where('type',1);
			})
	   		->orderBy('timestamp','DESC')
			->limit(500)	
	   		->get();
	   
	   return array('Log'=>CasterLogResource::collection($requests));//
    }
	
	public function getloggedrovertotals(Request $request){
		
		$dealer = $request->dealer;
	   	$company = $request->company;
		$green = $request->green;
	   	$subsarray = [];
	    $status = 1;
		$total = 0;
		
		//$states = RTKStatus::where('code','>',0)->get();
		$states = RTKStatus::where('code','>',0)->where('code','!=',6)->get();

		foreach($states as $state){
			
		$rovers = Rover::
				when($dealer > 0, function ($q) use($dealer) {
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
	
	
	
	public function loggedrovers(Request $request){
		
		
		
		$loggedrovers = array();
		$channels = array();
		$channelcount = 0;
		$dealer = $request->dealer;
		
		$rovers = Rover::
			where('last_mesg','>',0)
			->where('rtk_status','>',0)
			->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})			
			->get();	
		
		
//		$rovers = Rover::with(['getlastgga' => function ($query) {
//				$query->where('rtk_fix_status','>',0)->latest()->limit(1);
//				}])
//				->where('rtk_status','>',0)
//				->get();
//		
		
		
		//foreach($rovers as $rover){
			
//				$lastgga = CasterRequest::
//					where('rover_id',$rover->id)
//					->latest()
//					->limit(1)
//					->get();
//			
//			
//				if($lastgga !== null){
//			  		$lat = $lastgga[0]['latitude'];
//					$long = $lastgga[0]['longitude'];
//					$markers[] = array('pos'=>array($lat,$long),'type'=>1);			// start Marker	
//				}
			
//				if(isset($rover['getlastgga'][0])){
//			  		$lat = $rover['getlastgga'][0]['latitude'];
//					$long = $rover['getlastgga'][0]['longitude'];
//					$markers[] = array('pos'=>array($lat,$long),'type'=>1);			// start Marker	
//				}
		//}
	
	   return array('Rovers'=>RoverResource::collection($rovers));
    }
	
	public function getrovers(Request $request){
		
		$dealer = $request->dealer;
		$company = $request->company;
		$logged = $request->logged;
		$stext = strtoupper($request->stext);
		
		$loggedrovers = array();
		$channels = array();
		$channelcount = 0;
		
		
		$rovers = Rover:://with('getlastgga')
			when($dealer != 0, function ($q) use ($dealer) {
    			return $q->where('dealer',$dealer);
			})	
			->when($company != 0, function ($q) use ($company) {
    			return $q->where('company',$company);
			})
			->when($logged != 0, function ($q){
    			return $q->where('rtk_status','!=',0);
			})
			->when($stext != '', function ($q) use($stext) {
				return $q->where('title','LIKE',$stext.'%');
			})	
				//->where('rtk_status','>',0)
			->get();
		
	   return array('Rovers'=>RoverResourceMap::collection($rovers));
    }
	
	public function roverstats(){
		
		$stand = 0;
		$dgps = 0;
		$fix = 0;
		$float = 0;
		
		$rovers = Rover::with(['getlastgga' => function ($query) {
				$query->where('rtk_fix_status','>',0)->latest()->limit(1);
				}])
				->where('rtk_status','>',0)
				->get();
		
		
		foreach($rovers as $rover){
			switch($rover['rtk_status']){
				case 1:
				$stand++;
				break;
				case 2:
				$dgps++;
				break;
				case 4:
				$fix++;
				break;
				case 5:
				$float++;
				break;	
			}
					
			}
		
		$stats = array('Standalone'=>$stand,'DGPS'=>$dgps,'RTK Fix'=>$fix,'RTK Float'=>$float);
		return $stats;
	}
	


    public function show($id)
    {
        $client = Client::findorfail($id);
		return new ClientResource($client);
    }


    public function destroy($id)
    {
        $client = Client::findorfail($id);
		if($client->delete()){
			return new ClientResource($client);
		}
    }
	
	 public function testdatastore(Request $request)		//	GGA Message Data
    {
		 	
			if($request->gga == 1){
				
		 	$roverid = $request->rover;
		 	$bid = $request->basestation;
		 	$time = $request->time;
		 	$ggastring = $request->ggastring;
			$gga_string = explode(',',$ggastring);
				
			if($request->lat == null){	
			
			$deg = substr($gga_string[2],0,2);
			$min = substr($gga_string[2],2)/60;
			$lat = $deg + $min;
			
			$deg = substr($gga_string[4],0,3);
			$min = substr($gga_string[4],3)/60;
			$long = $deg + $min;
			if($gga_string[5] == 'W'){
				$long = $long * -1;
				};
				
			} else {
				
				$lat = $request->lat;
				$long = $request->long;
			}
				
			
			$utctime = date('H:i:s',$time);
			
			$rover = Rover::where('id',$roverid)->first();
			$name = $rover->username;
				
			$basestation = BaseStation::where('id',$bid)->first();
			$mount = $basestation->mount;	
				
			// Get current open session details and update
				
			$session = CasterSession::
						where('rover',$roverid)
						->where('status',1)	
						->orderby('id', 'desc')
						->first();
			
			if($session == null){return array('error'=>'no open session');} // If no open session return
			
			$current_ggacount = $session->num_ggas + 1;
			$current_fixcount = $session->num_fix;
				
			if($request->status == 9){
				$rtk_status = $gga_string[6];
			} else $rtk_status = $request->status;	
			
			$timenow = date('H:i:s');	
			$connectedtime = $session->connection_time; // Get time session connected 	
				
			if($rtk_status == 4){
				$session->time_to_fix = date('H:i:s',strtotime($timenow) - strtotime($connectedtime));
				$current_fixcount++;
				$session->num_fix =	$current_fixcount;
			}	
			
			$session->num_ggas = $current_ggacount;
			$session->quality = ($current_fixcount/$current_ggacount) * 100;	
			$session->basestation = $basestation->id;
			$session->save();
			
			// 
				
			$castreq = new CasterRequest;
			$castreq->name = $name;
			$castreq->group = 'TEST';
			$castreq->rover_id = $roverid;
			$castreq->basestationid = $bid;
			$castreq->GGA_string = $ggastring;
			$castreq->session_id = $session->id;	
			$castreq->utc_time_stamp = $utctime;
			$castreq->latitude = $lat;
			$castreq->longitude = $long;
			$castreq->rtk_fix_status = $rtk_status;
			$castreq->num_sateliites = $gga_string[7];
			$castreq->hdop = $gga_string[8];
			$castreq->altitude = $gga_string[9];
			$castreq->data_age = $gga_string[13];
			$castreq->timestamp = $time;
			$castreq->timedate = date('Y-m-d H:i:s',$time);
			$castreq->cgroup = 'test';
			$castreq->mount = $mount;
			$castreq->distance = 9999;
			$castreq->save();
			
			$rover = Rover::where('id',$roverid)->first();
			$rover->last_mesg = $castreq->id;
			$rover->rtk_status = $rtk_status; // Set last RTK status
				
			if($rtk_status == 4 && $rover->fix_time == 0){
				$rover->fix_time = $time;
				}	
			$rover->save();
		
	 		} else {		//	Create LOG message
		 
		 		$roverid = $request->rover;
		 		
				$rover = Rover::where('id',$roverid)->first();
				$name = $rover->username;
				$timenow = date('H:i:s');
				
				// Check if open session exists 
			
				$session = CasterSession::
						where('rover',$roverid)
						->where('status',1)	
						->orderby('id', 'desc')
						->first();

				
				// If no session and is client connect create new session record
				if($session == null && $request->code == 1){
					$session = new CasterSession;
					$session->rover = $rover->id;
					$session->status = 1;
					$session->connection_time = $timenow; // new session so set connecttion time to now
					$session->save();
					
					$session_id = $session->id;
					$session->session_id = $session_id;
					$session->save();
					}
				
				// If open session and is disconnect close current session status
				if($session != null && $request->code == 8){
					$connected_time = $session->connection_time;
					$session->connection_time = date('H:i:s',strtotime($timenow) - strtotime($connected_time));
					$session->status = 2;
					$session->save();
				}
				
				// --------------- //
				
				$message = CasterMessage::where('code',$request->code)->first();
				$message = $message->message;
				
				$log = new CasterLog;
				$log->rover = $roverid;
				$log->name = $name;
				$log->message = $message;
				$log->code = $request->code;
				$log->timestamp = $request->time;
				$log->type = 1;
				if($session != null){$log->session_id = $session->session_id;}
				$log->save();
				
				$rover->last_log = $log->id;
				if($session != null){$rover->session_id = $session->session_id;}
				
				if($request->code == 1){	// Connect
					$rover->last_connect = $request->time;	
				}
				if($request->code == 8){	// Disconnect
					$rover->last_connect = 0;
					$rover->fix_time = 0;
					$rover->rtk_status = 0;
				}
				
				$rover->save();
	 		}
		
		return;
		
	}
	
	
	
	public function castermessages(){
		$messages = CasterMessage::get();
		return array('Messages'=>CasterMessageResource::collection($messages));
	}
	
	public function roversessions(Request $request){
		
				$id = $request->id;
				$start = $request->start;
				//$start = '01-12-2021';
				if($start == ''){
					$sessions = CasterSession::
					where('rover',$id)
					->where('status',0)	
					->where('num_ggas','!=',0)	
					->orderBy('date_time','DESC')
					->limit(20)	
					->get();
				} else {
				$start = date('Y-m-d H:i:s',strtotime($start));
				$end = date('Y-m-d H:i:s',strtotime($request->end));
		
				$sessions = CasterSession::
				where('rover',$id)
				->where('status',0)		
				->where('date_time','>',$start)
				->where('date_time','<',$end)		
				->orderBy('date_time','DESC')
				->get();
					
				}
		
				return array('Sessions'=>CasterSessionLongResource::collection($sessions));
				
	}
	
	public function rovermessages(Request $request){
			
			$id = $request->rover;
			$sessionsel = $request->session;
			$start = $request->start/1000;
			$end = $request->end/1000;
			$last = $request->last;
			$polling = 0;
			$nosessions = 0;
		
			if($polling == 1){return;}
		
			$polling = 1;
		
			
			
			// If last set get last session id number from rover record
			if($last == 1){
				$rover = Rover::where('id',$id)->first();
				if($rover !== null){
				$sessionsel = $rover->session_id;
					
				$session = CasterSession::
					where('session_id',$sessionsel)
					->orderBy('id','DESC')
					->first();
					
				} else $nosessions = 1;
			}
		
		if($nosessions == 0){
		
			// TEST SWITCH $sessionsel = 51;//3;
		
			if($sessionsel == 0){   // All sessions
		
			// Get GGA records	
			$requestsGGA = CasterRequest::select('GGA_string AS message','timestamp','session_id','num_sateliites','hdop')
	   		->where('rover_id',$id)
			->where('timestamp','>',$start)
			->where('timestamp','<',$end)	
			->orderBy('timestamp','DESC')
			->get()
			->toArray();
				
			
			// Get Log records
			$requestsLog = CasterLog::select('message','timestamp','session_id')
	   		->where('rover',$id)
			->where('timestamp','>',$start)
			->where('timestamp','<',$end)		
			->orderBy('timestamp','DESC')
			->get()
			->toArray();
				
			//return array($requestsLog);	
				
			} else {	// GET SELECTED SESSION INFO ONLY
				
				
				$requestsGGA = CasterRequest::select('GGA_string AS message','id','timestamp','session_id','num_sateliites','hdop')
				->where('rover_id',$id)
				->where('session_id',$sessionsel)	
				->orderBy('timestamp','DESC')
				//->limit(5)	
				->get()
				->toArray();

				$requestsLog = CasterLog::select('message','timestamp','session_id')
				->where('rover',$id)
				->where('session_id',$sessionsel)	
				->orderBy('timestamp','DESC')
				//->limit(5)	
				->get()
				->toArray();
			}
		
			$messages = array_merge($requestsGGA,$requestsLog);
			$message  = array_column($messages, 'message');
			$timestamp = array_column($messages, 'timestamp');
		
			array_multisort($timestamp, SORT_DESC, $messages);
		
			$rover = Rover::where('id',$id)->get();
		
			// Get last sessions
			
		
			if($sessionsel == 0){		//	Get all sessions in time range
		
				$sessionslist = CasterSession::
						where('rover',$id)
						->where('created_at','>',date('Y-m-d H:i:s',$start))
						->where('created_at','<',date('Y-m-d H:i:s',$end))	
						->orderby('id', 'desc')
						->limit(5)
						->get()
						->toArray();
				
			//$sessionslist = array_reverse($sessionslist);	
				
			} else // Just get single session
			{
				$sessionslist = CasterSession::
						where('session_id',$sessionsel)
						->where('rover',$id)
						->orderby('id', 'desc')
						->limit(1)
						->get()
						->toArray();
			}
			
		
			//	Get markers and points
		
			$sessions = [];
			$markers = [];
			$points = [];
			$sessiondata = [];
			$fixtotal = 0;
		
			
		
			foreach($sessionslist as $session){
				
			
			$ggamessages = CasterRequest::
				where('session_id',$session['session_id'])
				->where('rover_id',$session['rover'])
				//->limit(1)	
				->get();
				
			
			//$channels[] = $ggamessages;	
		
			foreach($ggamessages as $ggamessage){
				
				
					$status = RTKStatus::where('code',$ggamessage->rtk_fix_status)->first();
					$color = $status->colour;
					if($status->code == 4){$fixtotal++;}
				
					$lat = $ggamessage->latitude;
					$long = $ggamessage->longitude;
					$points[] = array($lat,$long,$color,$ggamessage->timestamp); // Create lat/long line point
					}
				
				if(count($points) !== 0){
					$markers[] = array('pos'=>$points[0],'type'=>1,'name'=>$session['session_id']);			// start Marker	
					$markers[] = array('pos'=>$points[count($points) - 1],'type'=>2); 	// Icon Marker
				}
				
				$sessions[] = array('Session'=>$session,'Markers'=>$markers,'Points'=>$points);	
				
				$markers = [];
				$points = []; 
				}
		
		
			$polling = 0;
			
			return array('Sessions'=>$sessions,'Messages'=>$messages,'Rover'=>RoverResourceShort::collection($rover));
		
		}	else {	// No sessions found
			$polling = 0;
			return array('Sessions'=>[],'Messages'=>[],'Rover'=>[]);
		}
			
	} 
	
	public function rovermessage(Request $request){
			
			$id = $request->id;
			$time = $request->time;
		
			$request = CasterRequest::
	   		where('rover_id',$id)
			->where('timestamp',$time)
			->get();
				
			
			 return array('Requests'=>CasterRequestResource::collection($request));
		
//			
	} 
	
	public function lasteventmessages(Request $request){
	
			$reseller = $request->reseller;
			$dealer = $request->dealer;
			
				$rovers = Rover::select('id')
				->when($reseller > 0, function ($q) use($reseller) {
					return $q->where('business',$reseller);
				})	
				->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
				})
				->get();
		
			$messages = CasterLog::
				whereIn('rover',$rovers)
				->orderBy('timestamp','DESC')
				->limit(100)	
				->get();
			return array('Messages'=>CasterLogResource::collection($messages));
	}
	
	public function lasteventrovermessages(Request $request){
		
			$id = $request->id;
			if($request->session){$sessionid = $request->session;}
				else {
					$session = CasterSession::where('rover',$id)->orderBy('id','DESC')->first();
					$sessionid = $session->session_id;
				}
		
			$requestsGGA = CasterRequest::select('GGA_string AS message','id','timestamp','session_id','num_sateliites','hdop')
				->where('session_id',$sessionid)
				->where('rover_id',$id)
				->orderBy('timestamp','DESC')
				->limit(10)	
				->get()
				->toArray();
		
			
			// Get Log records
			$requestsLog = CasterLog::select('message','timestamp','session_id')
				->where('session_id',$sessionid)
	   			->where('rover',$id)
				->orderBy('timestamp','DESC')
				->limit(10)			
				->get()
				->toArray();
		
			
			$messages = CasterLog::
				orderBy('timestamp','DESC')
				->where('rover',$id)	
				->limit(10)	
				->get();
			
			$messages = array_merge($requestsGGA,$requestsLog);
			$message  = array_column($messages, 'message');
			$timestamp = array_column($messages, 'timestamp');
			array_multisort($timestamp, SORT_DESC, $messages);
			return array('Messages'=>$messages);
	}
	
	public function lasteventbsmessages(Request $request){
			$id = $request->id;
		
			if($id > 0 ){
			$messages = CasterRequestRTCM3::
				orderBy('timestamp','DESC')
				->where('basestation_id',$id)	
				->limit(6)	
				->get();
			} else {
				$messages = CasterRequestRTCM3::
				orderBy('timestamp','DESC')
				->limit(6)	
				->get();
			}
		
			return array('Messages'=>CasterRequestRTCM3Resource::collection($messages));
			//return array('Messages'=>$messages);
	}
	
	public function subsstats(Request $request){
		
		$dealer = $request->dealer;
		$start = strtotime($request->start.' 00:00:00');
		$end = strtotime($request->end.' 23:59:59');
		$steps = $request->steps;
		
		
		switch($steps){
			case 24:
			$period = 3600; // 1 hour
			break;
			case 7:
			$period = 3600 * 24; // 1 day
			break;
			case 12:
			$period = 3600 * 24 * 30; // 1 month
			break;	
			default:
			$period = 3600 * 24; // 1 day
			break;	
				
		}
		
		$pcount = 0;
		
		while ($pcount < $steps+1){
			$time = date('Y-m-d H:i:s',$start + ($pcount * $period));
			switch($steps){
				case 24:	
				$labels[] = substr(str_replace(':','',$time),10,5);
				break;
				case 7:
				$labels = ['mon','tue','wed','thu','fri','sat','sun','xx'];	
				break;
				case 12:
				$labels = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec','xx'];	
				break;	
				default:	// month
				$labels[] = substr($time,0,10);	
				break;	
			}
			
			$times[] = $time;	
			
			$pcount++;
		
		}
		
			array_pop($labels);
			$count = 0;
			$ds = [];
		
			while($count != $steps){	
				
				$stats = CasterStat::
					whereBetween('created_at',[$times[$count],$times[$count+1]])
					->when($dealer != 0, function ($q) use ($dealer){
						return $q->where('dealer',$dealer);
					})	
					->get();
				
				$maxlogins = 0;
					
				foreach($stats as $stat){
						if($stat->subs_active > $maxlogins){$maxlogins = $stat->subs_active;}
					}
				
				$ds[] = $maxlogins;
				$count++;
				}
				
				$dataset = array('label'=>'# logins','color'=>'#53c16b','data'=>$ds);	
		
				
				return array('labels'=>$labels,'datasets'=>$dataset);
	}
	
	public function orignetworkloginsold(Request $request){
	
		$business = $request->business;
		$dealer = $request->dealer;
		$start = strtotime($request->start.' 00:00:00');
		$end = strtotime($request->end.' 23:59:59');
		$steps = $request->steps;
		
		
		switch($steps){
			case 24:
			$period = 3600; // 1 hour
			break;
			case 7:
			$period = 3600 * 24; // 1 day
			break;
			case 12:
			$period = 3600 * 24 * 30; // 1 month
			break;	
			default:
			$period = 3600 * 24; // 1 day
			break;	
				
		}
		
		$pcount = 0;
		
		while ($pcount < $steps+1){
			$time = date('Y-m-d H:i:s',$start + ($pcount * $period));
			switch($steps){
				case 24:	
				$labels[] = substr(str_replace(':','',$time),10,5);
				break;
				case 7:
				$labels = ['mon','tue','wed','thu','fri','sat','sun','xx'];	
				break;
				case 12:
				$labels = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec','xx'];	
				break;	
				default:	// month
				$labels[] = substr($time,0,10);	
				break;	
			}
			
			$times[] = $time;	
			
			$pcount++;
		
		}
		
			array_pop($labels);
			$count = 0;
			$ds = [];
		
			while($count != $steps){	
				
				$stats = CasterStat::
					whereBetween('created_at',[$times[$count],$times[$count+1]])
					->when($business != 0, function ($q) use ($business){
						return $q->where('business',$business);
					})		
					->when($dealer != 0, function ($q) use ($dealer){
						return $q->where('dealer',$dealer);
					})	
					->get();
				
				$maxlogins = 0;
					
				foreach($stats as $stat){
						if($stat->rovers_total > $maxlogins){$maxlogins = $stat->rovers_total;}
					}
				
				$ds[] = $maxlogins;
				$count++;
				}
				
				$dataset = array('label'=>'# logins','color'=>'#53c16b','data'=>$ds);	
		
				
				return array('labels'=>$labels,'datasets'=>$dataset);
	}
	
	public function origrtkhistory(Request $request){
		
		$business = $request->business;
		$dealer = $request->dealer;
		$company = $request->company;
		
		if($request->date != ''){$today = $request->date;} 
			else {
				$today = date('Y-m-d');
				$start = strtotime(date('Y-m-d').' 00:00:00'); // time in seconds
				}
		
		$starttime = $start + (3600 * $request->start); // 0700
		$endtime = $start + (3600 * $request->end); // 1300
		$period = $request->period; // 1 hour 3600 1/2 hour 1800 600 10 mins
		$steps = ($endtime - $starttime)/$period + 1;
		$pcount = 0;
		
		
		
		while ($pcount < $steps){
			$time = date('H:i:s',$starttime + ($pcount * $period));
			$labels[] = substr(str_replace(':','',$time),0,4);
			$times[] = $time; 
			$pcount++;
		}
		
			$count = 0;
			$ds = [];
		
			while($count != $steps - 1){	
				
				$start = $today.' '.$times[$count];
				$end = $today.' '.$times[$count+1];
				
				
				
				$stats = CasterStat::
				//	whereBetween('created_at',[$start,$end])
					where('created_at','>',$start)
					->where('created_at','<',$end)	
					->when($business != 0, function ($q) use ($business){
						return $q->where('business',$business);
					})	
					->when($dealer != 0, function ($q) use ($dealer){
						return $q->where('dealer',$dealer);
					})	
					->get();
				
				$rtk_fix = 0;
				$rtk_float = 0;
				$rtk_dgps = 0;
				$rtk_standalone = 0;	
				$statcount = 0;	
					
				foreach($stats as $stat){
					if($stat->rover_gga_total > 0){
						$rtk_fix = $rtk_fix + $stat->rover_rtk_fix;
						$rtk_float = $rtk_float + $stat->rover_rtk_float;
						$rtk_dgps = $rtk_dgps + $stat->rover_rtk_dgps;
						$rtk_standalone = $rtk_standalone + $stat->rover_rtk_standalone;
						$statcount++;
					}
					
					
				}	
				
				if($rtk_fix > 0){$rtk_fix = round($rtk_fix/$statcount);}		// Get average for number of stats
				if($rtk_float > 0){$rtk_float = round($rtk_float/$statcount);} 
				if($rtk_dgps > 0){$rtk_dgps = round($rtk_dgps/$statcount);}
				if($rtk_standalone > 0){$rtk_standalone = round($rtk_standalone/$statcount);}	
					
				$ds[1][] = $rtk_standalone;
				$ds[2][] = $rtk_dgps;	
				$ds[3][] = $rtk_fix;
				$ds[4][] = $rtk_float;	
				
				$count++;
				
			}	// END STEP COUNT
		
				$states = RTKStatus::where('code','>',0)->where('code','!=',6)->get();
				$scount = 1;
				foreach($states as $state){
					$datasets[] = array('label'=>$state->message,'color'=>$state->colour,'data'=>$ds[$scount]);	
					$scount++;
				}
		
		
			return array('labels'=>$labels,'datasets'=>$datasets);
		
	}
	
	public function rtkhistory(Request $request){
		
		$reseller = $request->business;
		$dealer = $request->dealer;
		$company = $request->company;
		
		if($request->date != ''){$today = $request->date;} 
			else {
				$today = date('Y-m-d');
				$start = strtotime(date('Y-m-d').' 00:00:00'); // time in seconds
				}
		
		$starttime = $start + (3600 * $request->start); // 0700
		$endtime = $start + (3600 * $request->end); // 1300
		$period = $request->period; // 1 hour 3600 1/2 hour 1800 600 10 mins
		$steps = ($endtime - $starttime)/$period + 1;
		$pcount = 0;
		
		$rovers = [];
		if($reseller != 0){
			$rovers = Rover::select('id')->where('business',$reseller)->get();
		}
		
		if($dealer != 0){
			$rovers = Rover::select('id')->where('business',$reseller)->get();
		}
		
		
		while ($pcount < $steps){
			$time = date('H:i:s',$starttime + ($pcount * $period));
			$labels[] = substr(str_replace(':','',$time),0,4);
			$times[] = $time; 
			$pcount++;
		}
		
			$count = 0;
			$ds = [];
		
		
			while($count != $steps - 1){	
				
				$start = $today.' '.$times[$count];
				$end = $today.' '.$times[$count+1];
				
				
				$stats = [];
				$stats = CasterRequest:: 
					whereBetween('timedate',[$start,$end])
					->when($reseller != 0, function ($q) use ($rovers){
						return $q->whereIn('rover_id',$rovers);
					})		
					->when($dealer != 0, function ($q) use ($rovers){
						return $q->whereIn('rover_id',$rovers);
					})	
					->get();
//				
				
				
				$rtk_fix = 0;
				$rtk_float = 0;
				$rtk_dgps = 0;
				$rtk_standalone = 0;	
				$statcount = 0;	
					
				foreach($stats as $stat){
					
					switch($stat->rtk_fix_status){
						case 1:
						$rtk_standalone = $rtk_standalone + 1;
						break;
						case 2:
						$rtk_dgps = $rtk_dgps + 1;
						break;
						case 4:
						$rtk_fix = $rtk_fix + 1;
						break;
						case 5:
						$rtk_float = $rtk_float + 1;
						break;	
					}
					
				}
				
				$statcount = $statcount + count($stats);
				
				if($rtk_fix > 0){$rtk_fix = round($rtk_fix/$statcount*100);}		// Get average for number of stats
				if($rtk_float > 0){$rtk_float = round($rtk_float/$statcount*100);} 
				if($rtk_dgps > 0){$rtk_dgps = round($rtk_dgps/$statcount*100);}
				if($rtk_standalone > 0){$rtk_standalone = round($rtk_standalone/$statcount*100);}	
					
				$ds[1][] = $rtk_standalone;
				$ds[2][] = $rtk_dgps;	
				$ds[3][] = $rtk_fix;
				$ds[4][] = $rtk_float;	
				
				$count++;
				
			}	// END STEP COUNT
		
				$states = RTKStatus::where('code','>',0)->where('code','!=',6)->get();
				$scount = 1;
				foreach($states as $state){
					$datasets[] = array('label'=>$state->message,'color'=>$state->colour,'data'=>$ds[$scount]);	
					$scount++;
				}
		
			return array('labels'=>$labels,'datasets'=>$datasets);
		
	}
	
	public function networklogins(Request $request){
	
		$reseller = $request->business;
		$dealer = $request->dealer;
		$rovers = [];
		if($reseller != 0){
			$rovers = Rover::select('id')->where('business',$reseller)->get();
		}
		
		if($dealer != 0){
			$rovers = Rover::select('id')->where('business',$reseller)->get();
		}
		
		//return $rovers;
		//$start = strtotime($request->start.' 00:00:00');
		//$end = strtotime($request->end.' 23:59:59');
		
		$today = date('Y-m-d');
		$start = strtotime($today.' 00:00:00');
		$end = strtotime($today.' 23:59:59');
		
		
		$steps = 24;//$request->steps;
		
			
		switch($steps){
			case 24:
			$period = 3600; // 1 hour
			break;
			case 7:
			$period = 3600 * 24; // 1 day
			break;
			case 12:
			$period = 3600 * 24 * 30; // 1 month
			break;	
			default:
			$period = 3600 * 24; // 1 day
			break;	
				
		}
		
		$pcount = 0;
		
		while ($pcount < $steps+1){
			$time = date('Y-m-d H:i:s',$start + ($pcount * $period));
			switch($steps){
				case 24:	
				$labels[] = substr(str_replace(':','',$time),10,5);
				break;
				case 7:
				$labels = ['mon','tue','wed','thu','fri','sat','sun','xx'];	
				break;
				case 12:
				$labels = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec','xx'];	
				break;	
				default:	// month
				$labels[] = substr($time,0,10);	
				break;	
			}
			
			$times[] = $time;	
			
			$pcount++;
		
		}
		
			array_pop($labels);
			$count = 0;
			$ds = [];
		
			while($count != $steps){	
				
				$stats = CasterLog::
					whereBetween('date_time',[$times[$count],$times[$count+1]])
					->where('code',1)
					->where('type',1)	
					->when($reseller != 0, function ($q) use ($rovers){
						return $q->whereIn('rover',$rovers);
					})		
					->when($dealer != 0, function ($q) use ($rovers){
						return $q->whereIn('rover',$rovers);
					})	
					->get();
				
				if($stats !== null){$maxlogins = count($stats);} else $maxlogins = 0;
					
//				foreach($stats as $stat){
//						if($stat->rovers_total > $maxlogins){$maxlogins = $stat->rovers_total;}
//					}
				
				$ds[] = $maxlogins;
				$count++;
				}
				
				$dataset = array('label'=>'# logins','color'=>'#53c16b','data'=>$ds);	
		
				
				return array('labels'=>$labels,'datasets'=>$dataset);
	}
	
	public function ntripclienthistory(Request $request){
		
		$dealer = $request->dealer;
		$start = strtotime($request->start.' 00:00:00');
		$end = strtotime($request->end.' 23:59:59');
		$steps = $request->steps;
		
		$rovers = Rover::select('id')
				->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
				})
				->get();
		
		$colors = ['#53c16b','#9dc6e9','#e8be32','#d44f3b','pink','orange','white','grey'];
		
		switch($steps){
			case 24:
			$period = 3600; // 1 hour
			break;
			case 7:
			$period = 3600 * 24; // 1 day
			break;
			case 12:
			$period = 3600 * 24 * 30; // 1 month
			break;	
			default:
			$period = 3600 * 24; // 1 day
			break;	
				
		}
		
		$pcount = 0;
		
		while ($pcount < $steps+1){
			$time = date('Y-m-d H:i:s',$start + ($pcount * $period));
			switch($steps){
				case 24:	
				$labels[] = substr(str_replace(':','',$time),10,5);
				break;
				case 7:
				$labels = ['mon','tue','wed','thu','fri','sat','sun','xx'];	
				break;
				case 12:
				$labels = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec','xx'];	
				break;	
				default:	// month
				$labels[] = substr($time,0,10);	
				break;	
			}
			
			$times[] = $time;	
			
			$pcount++;
		
		}
		
			array_pop($labels);
			$count = 0;
			$ds = [];
			$agents = [];
		
			while($count != $steps){	
				
				$sessions = CasterSession::
					whereBetween('date_time',[$times[$count],$times[$count+1]])
					->whereIn('rover',$rovers)
					->get();
				
				foreach($sessions as $session){
						if(!in_array($session->user_agent,$agents)){
							$agents[] = $session->user_agent;
						}
					}
				
				$agentkey = 0;
				
				foreach($agents as $agent){
				$sessioncount = CasterSession::
						whereBetween('date_time',[$times[$count],$times[$count+1]])
						->where('user_agent',$agent)
						->count();

						if(count($sessions) != 0){
							$value = round($sessioncount/count($sessions) * 100);
						} else $value = 0;

						//$ds[$agentkey][] = array('agent'=>$agent,'value'=>$value,'color'=>$colors[$agentkey]);
						$ds[$agentkey][] = $value;
						$agentkey++;
					}
				
				
				$count++;
				}
		
				$agentkey = 0;
		
				foreach($agents as $agent){
					$dataset[] = array('label'=>$agent,'color'=>$colors[$agentkey],'data'=>$ds[$agentkey]);	
					$agentkey++;	
				}
				
				return array('labels'=>$labels,'datasets'=>$dataset);
		
	}
	
	public function datausage(Request $request){
		
		$business = $request->business;
		$dealer = $request->dealer;
		$company = $request->company;
		$rover = $request->rover;
		
			$rovers = Rover::select('id')
				->when($rover > 0, function ($q) use($rover) {
					return $q->where('id',$rover);
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
				->get();	
		
				$total = 0;
		
				
				$simmdata = CasterSimm::
					whereIn('rover',$rovers)
					->get();
		
				foreach($simmdata as $data){
					$total = $total + $data->datausedmonth; //(in bytes)
				}
				
				$allowance = 500 * 1000000 * count($simmdata); // 500MB per card
				if($allowance !== 0){$used = round($total/$allowance * 100);} else $used = 0;
				$remaining = 100 - $used;
				
				$dataset[] = array('label'=>'used','color'=>'#d44f3b','value'=>$used);
				$dataset[] = array('label'=>'remaining','color'=>'#53c16b','value'=>$remaining);
				
			
		
				return array('data'=>$dataset,'allowance'=>round($allowance/1000/1000),'used'=>round($total/1000/1000),'simms'=>count($simmdata));
		
		
	}
	
	public function lastfiveconnections(Request $request){
		
			$statusarray = [];
			$labels = [];
			$data = [];
			$totaltime = 0;
		
			$green = $request->green;
			$rover = $request->rover;
			//$states = RTKStatus::where('code','>',0)->get();
			$states = RTKStatus::where('code','>',0)->where('code','!=',6)->get();

			$sessions = CasterSession::
				where('rover',$rover)
				->where('num_ggas','>',0)	
				->orderBy('id','DESC')
				->limit(5)		
				->get()
				->toArray();
		
			foreach($sessions as $session){
				$totaltime = $session['connection_time'] + $totaltime;	
			}
		
			foreach($states as $state){
				
				foreach($sessions as $session){
					if($session['num_ggas'] != 0){
						$data[] = round($session[$state->field]/$session['num_ggas'] * 100);
						} else $data[] = 0;
					
					if(!in_array($session['session_id'],$labels)){
						$labels[] = $session['session_id'];
					}
				}
				
				if($green == 1){$color= $state->colour2;} else $color = $state->colour;
				
				$statusarray[] = array('label'=>$state->message,'color'=>$color,'data'=>$data);
				
				$data = [];
			}
		
			
			return array('labels'=>$labels,'datasets'=>$statusarray,'totaltime'=>$totaltime);
		
	}
	
	
//	public function oldlastfiveconnections(Request $request){
//		
//			$status = 0;
//			$statusarray = [];
//			$green = $request->green;
//			$rover = $request->rover;
//
//			$states = RTKStatus::where('code','>',0)->get();
//
//			foreach($states as $state){
//				
//				$statusarray[$status]['label'] = $state->message;
//				if($green == 1){$statusarray[$status]['color'] = $state->colour2;}
//					else $statusarray[$status]['color'] = $state->colour;
//			
//			$labels[] = $state->code; 	
//			$field = $state->field;
//				
//			$datas = CasterSession::
//				where('rover',$rover)
//				->where('num_ggas','>',0)	
//				->orderBy('id','DESC')
//				->limit(5)		
//				->get()
//				->toArray();
//				
//				foreach($datas as $data){
//				$statusarray[$status]['data'][] = $data[$field];	
//				}
//				
//				$status++;
//			}
//		
//			return array('labels'=>$labels,'datasets'=>$statusarray);
//		
//	}
}

//public function oldrtkhistory(Request $request){
//		
//		$green = $request->green;
//		$date = $request->date;
//		$roverid = $request->rover;
//		$dealer = $request->dealer;
//		
//		if($roverid == 0){
//			$roverslist = Rover::select('id')
//				->when($dealer > 0, function ($q) use($dealer) {
//					return $q->where('dealer',$dealer);
//				})
//				->get()
//				->toArray();
//		}
//		
//		$labels = ['0000','0100','0200','0300','0400','0500','0600','0700','0800','0900','1000','1100','1200','1300','1400','1500','1600','1700','1800','1900','2000','2100','2200','2300'];
//		$times = ['00:00:00','01:00:00','02:00:00','03:00:00','04:00:00','05:00:00','06:00:00','07:00:00','08:00:00','09:00:00','10:00:00','11:00:00','12:00:00','13:00:00','14:00:00','15:00:00','16:00:00','17:00','18:00:00','19:00:00','20:00:00','21:00:00','22:00:00','23:00:00'];
//		
//			if($date != ''){$today = $date;} else $today = date('Y-m-d');
//			$data = [];
//			$count = 0;
//		
//			$states = RTKStatus::where('code','>',0)->get();
//		
//			foreach($states as $state){
//			
//			while($count != 23){	
//				
//				$start = $today.' '.$times[$count];
//				$end = $today.' '.$times[$count+1];
//				
//				if($roverid == 0){
//				
//				$statetotal = CasterRequest::select('rover_id')	//	Get state total
//				->whereBetween('timedate',[$start,$end])
//				->whereIn('rover_id',$roverslist)		
//				->get()
//				->count();
//				
//				$rovers = CasterRequest::select('rover_id')	//	Get rover count for state in time period
//				->whereBetween('timedate',[$start,$end])	
//				->where('rtk_fix_status',$state->code)
//				->whereIn('rover_id',$roverslist)		
//				->get()
//				->count();
//					
//				} else {
//					
//					
//				$statetotal = CasterRequest::select('rover_id')	//	Get state total
//				->whereBetween('timedate',[$start,$end])
//				->where('rover_id',$roverid)		
//				->get()
//				->count();
//				
//				$rovers = CasterRequest::select('rover_id')	//	Get rover count for state in time period
//				->whereBetween('timedate',[$start,$end])	
//				->where('rtk_fix_status',$state->code)
//				->where('rover_id',$roverid)			
//				->get()
//				->count();
//					
//				}
//				
//				//$data[] = array($rovers,$statetotal);
//				
//				if($statetotal != 0){
//					$data[] = round($rovers/$statetotal * 100);
//				} else $data[] = 0;
//				
//				$count++;
//				
//			}
//				$datasets[] = array('label'=>$state->message,'color'=>$state->colour,'data'=>$data);
//				$count = 0;
//				$data = [];
//				
//			}
//		
//			return array('labels'=>$labels,'datasets'=>$datasets);
//		
//	}
