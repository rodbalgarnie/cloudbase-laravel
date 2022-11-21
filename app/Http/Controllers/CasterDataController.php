<?php // Version 091122))

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\CasterRequest;
use App\CasterRequestRTCM3;
use App\CasterLog;
use App\CasterSession;
use App\CasterEvent;
use App\Rover;
use App\BaseStation;
use App\CasterDealer;
use App\CasterNtripSubscription;
use App\System;
use DB;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
	
class CasterDataController extends Controller
{
	public function testgetcaster(Request $request){
		
		return 'SWITCHED OFF FOR TESTING';
		
	}
	
	public function getcaster(Request $request){  
		
//		
		
		// Check polling status
		$system = System::get()->first(); 
		
		$lastpoll = time() - strtotime($system->lastcasterpoll);
		$now = date('Y-m-d H:i:s',time());
		if( $lastpoll > 6000){
				if($system->casterpolloffline == 0){$this->createevent(0,200,'ERROR','system',$now);}
				$system->casterpolloffline = 1;
			} else {
				if($system->casterpolloffline == 1){$this->createevent(0,201,'OK','system',$now);}
				$system->casterpolloffline = 0;
			}
		
		//if($system->polling == 1){return 'getting data';}
//	
		$system->polling = 1;
		$system->save();
		
		$select = $request->select;
		$logsbases = 0;
		$logsrovers = 0;
		$ggas = [0,0,0];
		$rtcm = 0;
		$error = '';
	
		$savelog = 0;
		
		
		$logsbases = $this->storelogsbases();
		
		$logsrovers = $this->storelogsrovers();
		
		$sessionlist = $logsrovers[1];
		
		
		if(count($sessionlist) > 0){
			$ggas = $this->storegpgga($sessionlist);
		}
		
		$rtcm = $this->storertcm();
		
		$checkhanging = $this->checkhanging();
		
		$savelog = 1;	
		
		
		$updatepolling = System::where('id',1)->update(['polling' => 0,'lastcasterpoll' =>  date('Y-m-d H:i:s',time())]);
		
		
		// Store Polling Log
		if($savelog == 1){
		
		
		return array(
			'error'=>$error,
			'hung Rovers'=>$checkhanging,
			'logsbases'=>$logsbases['processed'],
			'logsrovers'=>$logsrovers[0],
			'ggas'=>$ggas[0],
			'rtcm3'=>$rtcm,
			'rovers'=>$ggas[1],
			'session list'=>$sessionlist
			);
		
		} else return 'all done';
				
	}
	
	
	public function checkhanging(){
		
		// Deal with hanging rovers
		
		$count = 0;
		$test = [];
		
		$castarovers = DB::connection('mysql2')	//	Get all Casta disconnected rovers
			->table('subscriptions')
			->where('In_use',0)
			->get()
			->toArray();
		
		
		$crmrovers = Rover::				//	Get all CRM connected rovers
				where('archive',0)
				->where('rtk_status','>',0)
				->get()
				->toArray();	
			
		
		foreach ($castarovers as $castarover){ 		//	Check CRM rover against disconnect list and deal with it
		
			$title = $castarover->Username;
			$roverfilter = array_values(array_filter($crmrovers,function($crmrover) use ($title) { return $crmrover['title'] == $title;}));
			
			if($roverfilter){
				$rover = Rover::where('title',$title)->first();
				$rover->rtk_status = 0;
				$rover->session_id = 0;
				$rover->save();
				//$count++;
				$test[] = $rover;
			}
			
		}
		
		return count($test);
	}
	
	

	public function storelogsbases(){	//	Process Log Messages
    	
		$error = null;
		$bases = BaseStation::get();
		$system = System::get()->first();
		$lastlogid = $system->lastlogbaseid;
		$processedbases = 0;
		
		
		foreach($bases as $base){
		
			
		$logs = DB::connection('mysql2')
			->table('logs')
			->where('id','>',$lastlogid)
			->where('Basestation',$base->title)	
			->get();
		
		if($logs->isNotEmpty()){
			
			
			foreach($logs as $data){
			
			
				if($data->Message_code == 2 && $data->Type_code == 3){
					$this->createevent(0,202,'RESTART','system',$data->Timestamp);
				}
			
		
				$log = new CasterLog;
				$log->caster_no = 1;
				$log->name = $base->title;
				$log->basestation = $base->id;
				$log->message = $data->Description;
				$log->code = $data->Message_code;
				$log->timestamp = strtotime($data->Timestamp);
				$log->date_time = date('Y-m-d H:i:s',strtotime($data->Timestamp));
				$log->type = $data->Type_code;
				$log->session_id = $data->Session_id;
				$log->save();
				$processedbases++;
			}
			
			$system->lastlogbaseid = $logs->last()->id;
			$system->save();
			
	
		} // End if empty
			
			
		} // End bases loop
		
		
		 
			return array('processed' => $processedbases);
	}
	
	
	public function storelogsrovers(){	//	Process Rover Log Messages
		
		$system = System::get()->first();
		$lastlogid = $system->lastlogroverid;
		$processedrovers = 0; 
		$sessionslist = [];
		 
		$subs = CasterNtripSubscription::with('rovers')
			->where('status',3)
			->get();
		
		
		foreach($subs as $sub){$rovers[] = $sub->rovers;}
		
		
		foreach($rovers as $rover){
			
			$logs = DB::connection('mysql2')
			->table('logs')
			->where('id','>',$lastlogid)
			->where('Username',$rover->username)	
			->get();
		
		if($logs->isNotEmpty()){
			
			foreach($logs as $data){
				
			if($data->Basestation !== null){	
				
				$basestation = BaseStation::where('title',$data->Basestation)->first();
				
				switch($data->Message_code){
					case 2: // Connect to nearest basestaion	
					$rover->last_connect = strtotime($data->Timestamp);
					$rover->rtk_status = 9;
					$rover->session_id = $data->Session_id;
					
					
					$checksession = CasterSession::where('session_id',$data->Session_id)->first();
					if($checksession == null){	
						$basestation = BaseStation::where('title',$data->Basestation)->first();
						$session = new CasterSession;
						$session->session_id = $data->Session_id;
						$session->rover = $rover->id;
						$session->basestation = $basestation->id;
						$session->date_time = date('Y-m-d H:i:s',strtotime($data->Timestamp));
						$session->status = 1;
						$session->save();
					}
					if(!in_array($data->Session_id,$sessionslist)){$sessionslist[] = $data->Session_id;}
					break;	
						
					case 6: // Transfer to nearest basestation
					$session = CasterSession::
					where('session_id',$data->Session_id)
					->where('rover',$rover->id)	
					->orderby('id', 'desc')
					->first();	
					if($session !== null){
						$basestation = BaseStation::where('title',$data->Basestation)->first();
						$session->basestation = $basestation->id;
						$session->save();	
						}
					if(!in_array($data->Session_id,$sessionslist)){$sessionslist[] = $data->Session_id;}	
					break;	
						
					
					case 8: // End connection
					$rover->rtk_status = 0;
					$rover->session_id = 0;
						
					$session = CasterSession::
					where('session_id',$data->Session_id)
					->where('rover',$rover->id)	
					->orderby('id', 'desc')
					->first();	
					if($session !== null){	
						//$session->connection_time = strtotime($data->Timestamp) - strtotime($session->date_time);
						$session->speed = 0;
						$session->status = 0;
						$session->save();	
						}
					break;
					if(!in_array($data->Session_id,$sessionslist)){$sessionslist[] = $data->Session_id;}	
					}
			
						$log = new CasterLog;
						$log->caster_no = 1;
						$log->name = $rover->username;
						$log->rover = $rover->id;
						if(isset($basestation->id)){$log->basestation = $basestation->id;}
						$log->message = $data->Description;
						$log->code = $data->Message_code;
						$log->timestamp = strtotime($data->Timestamp);
						$log->date_time = date('Y-m-d H:i:s',strtotime($data->Timestamp));
						$log->type = $data->Type_code;
						$log->session_id = $data->Session_id;
						$log->save();
				
					
					$processedrovers++;
					$rover->last_log = $log->id;
					$rover->save();
				
					$system->lastlogroverid = $logs->last()->id;
					$system->save();
			
			} // End no data check 
				
			} // End logs loop
			
			} // End log empty  loop 
			
		} // End Rover loop
		
		// Get all active sessions
		
		$rovers = DB::connection('mysql2')
			->table('subscriptions')
			->where('In_use',1)	
			->get();
		
		if($rovers->isNotEmpty()){
			
			foreach($rovers as $rover){
			
				$rover = DB::connection('mysql2')
					->table('logs')
					->where('Username',$rover->Username)
					->orderBy('id','DESC')
					->limit(1)
					->get();
				
				$session_id = $rover[0]->Session_id;//[0]['Session_id'];
				
				if(!in_array($session_id,$sessionslist)){$sessionslist[] = $session_id;}
			}
			
			
			
		}
		 
		    
			return array($processedrovers,$sessionslist);
	}
	
	
	
	public function storegpgga($sessionlist){		//	Process GGA Message Data
    	
		$system = System::get()->first();
		$lastggaid = $system->lastggaid;
		$lastid = 0;
		$sessionsprocessed = 0;
		$processed = 0;	
		
		
		$sessions = CasterSession::with('rovers')
			->whereIn('session_id',$sessionlist)
			->orderBy('id','ASC')
			->get();
		
	
	foreach($sessions as $session){
		
		$num_fix = $session->num_fix;
		$num_dgps = $session->num_dgps;
		$num_float = $session->num_float;
		$num_stand = $session->num_standalone;
		$bytes_sent = $session->bytes_sent;
		$bytes_rcvd = $session->bytes_rcvd;
			
		$ggas =  DB::connection('mysql2')
			->table('client_msgs')
			->where('id','>',$lastggaid)
			->where('Session_id',$session->session_id)
			->orderBy('id','ASC')
			->get();
		
		$test[] = array($session->session_id,count($ggas));
	
		
	if($ggas->isNotEmpty()){
		
		if($ggas->last()->id > $lastid){$lastid = $ggas->last()->id;}
		
		foreach($ggas as $data){
			
			$ggastring = $data->GGA;
		
		if(strlen($ggastring) > 20){
			
			$gga_string = explode(',',$ggastring);
			
			if(isset($gga_string[2])){
				
			if(is_numeric(substr($gga_string[2],0,2)) && is_numeric(substr($gga_string[2],2))){	
			
			$deg = substr($gga_string[2],0,2);
			$min = substr($gga_string[2],2)/60;
			
//			if(is_numeric($deg) && is_numeric($min))
//			{
					$lat = $deg + $min;

					$deg = substr($gga_string[4],0,3);
					$min = substr($gga_string[4],3)/60;
					$long = $deg + $min;
					if($gga_string[5] == 'W'){
						$long = $long * -1;
					};

					$rtk_status = $gga_string[6];
					$name = $data->Username;
					$bsname = $data->Basestation;

					$rover = $session->rovers[0];
						
					$currentrtk_status = $rover->rtk_status;
					$gga_str = $data->GGA;
						
					$castreq = new CasterRequest;
					$castreq->caster_no = 1;	
					$castreq->name = $name;
					$castreq->rover_id = $rover->id;
					$castreq->session_id = $data->Session_id;
					$castreq->GGA_string = $gga_str;
					$castreq->utc_time_stamp = date('H:i:s',strtotime($data->Timestamp));
					$castreq->latitude = $lat;
					$castreq->longitude = $long;
					$castreq->rtk_fix_status = $rtk_status;
					$castreq->num_sateliites = $gga_string[7];
					$castreq->hdop = $gga_string[8];
					$castreq->altitude = $gga_string[9];
					$castreq->data_age = $gga_string[13];
					$castreq->timestamp = strtotime($data->Timestamp);
					$castreq->timedate = $data->Timestamp;
					$castreq->mount = $data->Basestation;
					$castreq->distance = $data->Distance/1000;
					if(isset($gga_string[14])){
						$castreq->basestationid = substr($gga_string[14],0,3);
					}
					$castreq->save();

					
					$processed++;
					
					
						if($session->time_to_fix == 0 && $rtk_status == 4){
							$session->time_to_fix = strtotime($data->Timestamp) - strtotime($session->date_time);
							}

						switch($rtk_status){
							case 1:
							$num_stand++;		
							break;
							case 2:
							$num_dgps++;		
							break;
							case 4:
							$num_fix++;		
							break;
							case 5:
							$num_float++;	
							break;
							}
							
							$time = 10;
							$speed = $this->calculatespeed($session->last_lat,$session->last_long,$lat,$long,$time);
							$bytes_sent = $bytes_sent + $data->Bytes_sent;
							$bytes_rcvd = $bytes_rcvd + strlen($data->GGA);
							
						
						
				
				} // End NUmeric check for lat long
				
				}
			
				} // End GGA string check
			
					if(isset($castreq->id)){
					$rover = Rover::where('username',$data->Username)->first();
					
						if($rover !== null){	
						$rover->last_mesg = $castreq->id;
						$rover->rtk_status = $rtk_status; // Set last RTK status
						$rover->save();
						}
					}
				
			}	// End GGA Loop
		
			$session->connection_time = strtotime($data->Timestamp) - strtotime($session->date_time);
			$session->num_fix = $num_fix;
			$session->num_float = $num_float;
			$session->num_standalone = $num_stand;
			$session->num_dgps = $num_dgps;
			$session->num_ggas = $session->num_ggas + count($ggas);
			$session->bytes_sent = $bytes_sent;
			$session->bytes_rcvd = $bytes_rcvd ;
			$session->user_agent = $data->User_agent;
			$session->quality = $session->num_fix/$session->num_ggas * 100;
			$session->last_lat = $lat;
			$session->last_long = $long;
			$session->caster_no = $session->Caster_number;
			$session->speed = $speed;
			$session->save();
		
			$sessionsprocessed++;	
			
		
			$system->lastggaid = $lastid;
			$system->save();
			
		
			} // End GGAS not empty
		
			
			
		} // End Session loop
		

		return array($processed,$sessionsprocessed,0);
		
	}
	
	public function calculatespeed($latitudeFrom,$longitudeFrom,$latitudeTo,$longitudeTo,$time){
		
		  if($latitudeFrom == null || $longitudeFrom == null){return 0;}
		// convert from degrees to radians
		  $latFrom = deg2rad($latitudeFrom);
		  $lonFrom = deg2rad($longitudeFrom);
		  $latTo = deg2rad($latitudeTo);
		  $lonTo = deg2rad($longitudeTo);

		  $latDelta = $latTo - $latFrom;
		  $lonDelta = $lonTo - $lonFrom;

		  $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
			cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
		  $distance = $angle * 6371;		// kms
		  $time = $time/3600; // hour)
		  $speed = $distance/$time;		
		  return $speed;
	}

	
	
	
	public function storertcm(){		// RTCM03 Messsges
    
		$bases = BaseStation::select('id','title','status')->get()->toArray();
		
		$system = System::get()->first();
		$lastrtcm3id = $system->lastrtcm3id;
		$processed = 0;
		$now = time();
		$datetime = date('Y-m-d H:i:s',$now);
		$chunkSize = 10000;
		$recordsRemaining = true;
    	$lookupsCompleted = 0;
		
		while($recordsRemaining){

				$skip = ($chunkSize*$lookupsCompleted);
		
				$rtcm3 =  DB::connection('mysql2')
							->table('source_msgs')
							->where('id','>',$lastrtcm3id)
							->whereIn('Message',[1004,1012,1003,1011]) // Changed from 1004,1012,1127,109 18/05/22)
							->take($chunkSize)
							->skip($skip)
							->get()
							->toArray();
			
				$processed = $processed + $this->processRTCM3($rtcm3,$bases);
				$lookupsCompleted++;
				if(count($rtcm3) < $chunkSize){$recordsRemaining = false;}
			
		}
		
				// Update Base status
		
				$now = time();
				$datetime = date('Y-m-d H:i:s',$now);
		
				foreach($bases as $base){
					
					$lastrtcm = CasterRequestRTCM3::where('basestation_id',$base['id'])->orderBy('id','DESC')->first();
					if($lastrtcm !== null){
					
						$lastrtcm_time = $lastrtcm->timestamp;
						
						
						if($now - strtotime($lastrtcm_time) > 180){$status = 0;} else $status = 1;

						if($base['status'] !== $status){
							if($status == 0){$eventtype = 100;} else $eventtype = 101;
							$this->createevent($base['id'],$eventtype,$base['title'],'base',$datetime);
							}

						$basestation = BaseStation::where('id',$base['id'])->first();
						$basestation->lastrtcm3 = $lastrtcm->timestamp;
						$basestation->status = $status;	
						$basestation->save();
						
					}
					
				}
		
			return $processed;
		 
	 }
	
	public function processRTCM3($messages,$bases){
			
			
			$status = 0;
			$last_gsats = 0;
			$last_rsats = 0;
			$string = [];
			$last_id = 0;
			
			
			foreach($messages as $data){
				
				$title = $data->Basestation;
				$base = array_values(array_filter($bases,function($base) use ($title) { return $base['title'] == $title;}));
			
				if(isset($base[0])){
					
				$numsats = $data->Num_Satellites;
				switch($data->Message){
					case 1003:
					case 1004:
					$gsats = $numsats;
					$last_gsats = $numsats;
					$rsats = 0;	
					break;
					case 1011:
					case 1012:
					$rsats = $numsats;
					$last_rsats = $numsats;	
					$gsats = 0;	
						
					$string[] = array('test'=>$data->id,'timestamp'=>$data->Timestamp,'session_id'=>$data->Station_id,'basestation_id'=>$base[0]['id'],'num_satellites_g'=>$gsats,'num_satellites_r'=>$rsats,'message'=>$data->Message);	
				}
				
				
				}
				
				$last_id = $data->id;
				
			}
		
				if($last_id !== 0){
					$system = System::get()->first();
					$system->lastrtcm3id = $last_id;
					$system->save();
				}
		
			
			$test = CasterRequestRTCM3::upsert(
				$string, ['test', 'session_id','basestation_id','num_satellites_g','num_satellites_r']
			);
		
				$processed = count($messages);
		
				return $processed;	
				
					
	}		
			
	
	
	public function updateBSstatus(){
		
		$bases = Basestation::get();
		foreach ($bases as $base){
			$currentstatus = $base->status;
			$now = time();
			if($now - strtotime($base->lastrtcm3) > 180){$status = 0;} else $status = 1;
			
			$datetime = date('Y-m-d H:i:s',$now);
			
			if($currentstatus !== $status){
				if($status == 0){$eventtype = 100;} else $eventtype = 101;
				$this->createevent($base->id,$eventtype,$base->title,'base',$datetime);
				}
			
			$base->status = $status;
			$base->save();
		}
		
		return;
		
	}
	
	public function createevent($baseid,$type,$text,$group,$datetime){
		
		$event = new CasterEvent;
		$event->type = $type;
		$event->basestation = $baseid;
		$event->text = $text;
		$event->eventgroup = $group;
		$event->datetime = $datetime;
		$event->save();
	}
		 
	

//	public function disconnectclient(Request $request){
//		
//		
//		$link = 'http://charlie:1234@cb-rtk-casta-uk.com:2101/admin?mode=kick&argument=49';
//		//$link = 'http://charlie:1234@nickabbeyservices.co.uk:2101/admin?mode=rehash';
//		
//		$response = Http::withHeaders([			//	Get data per device from Flespi
//    				'accept' => 'application/json',
//					//'Authorization' => 'FlespiToken XyeV5xS72tgx3e5XjuDeS9evvxKWJdZxz1guoXregvA7xvUGNmJvlLOE8pqHqf2P'
//					])
//  					->get($link);
//		
//		return $response;
//	
//	}
	
	  
}