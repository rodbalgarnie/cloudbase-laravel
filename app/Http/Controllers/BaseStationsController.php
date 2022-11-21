<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BaseStation;
use App\BaseStationStatus;
use App\CasterRequestRTCM3;
use App\CasterRequestSatellite;
use App\CasterSatellitePRN;
use App\Http\Controllers\Controller;
use App\Http\Resources\BaseStationResource as BaseStationResource;
use App\Http\Resources\CasterRequestSatelliteResource as CasterRequestSatelliteResource;
use App\Http\Resources\CasterRequestRTCM3Resource as CasterRequestRTCM3Resource;
use Illuminate\Support\Facades\Http;

class BaseStationsController extends Controller
{

	
	public function indexdata(Request $request)
    {
	   $id = $request->id;
	   
	   $bases = BaseStation::
	   		when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
			->where('title','!=','NULL')	
		 	->get();
		
		$chartdata  = $this->basestationstats();	
	   
	   return array('basestations'=>BaseStationResource::collection($bases),'chartdata'=>$chartdata);//
    }
	
	
   public function index(Request $request)
    {
	   $id = $request->id;
	   
	   $bases = BaseStation::
	   		when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
			->where('title','!=','NULL')	
		 	->get();
	   
	   return array('basestations'=>BaseStationResource::collection($bases));//
    }
	
	public function basestationstats()
    {
	   	$states = BaseStationStatus::get();
	   	$totals = [];
		$total = 0;	
		foreach($states as $state){
			$bases = BaseStation::where('status',$state->code)->get()->toArray();
			$totals[] = array('label'=>$state->message,'value'=>count($bases),'color'=>$state->colour);
			$total = $total + count($bases);
		}
		$totals[0]['total'] = $total;
		return array('total'=>$total,'data'=>$totals);
	}
	
	public function lasteventbsmessages($id){
			
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
	}
	
	public function getlasteventbsmessages(Request $request){
			
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
	}


	
	public function getbasestation(Request $request)
    {
	   $id = $request->id;
	   
	   $basedetail = BaseStation::
	   		where('id',$id)
		 	->first();
		
		$basedetail->latitude = round($basedetail->latitude,10);
		$basedetail->longitude = round($basedetail->longitude,10);
		
		$status = BaseStationStatus::where('code',$basedetail->status)->first();
		$basedetail->statustext = $status->message;
		
		$rtcm3 = CasterRequestRTCM3::
			where('basestation_id',$id)
			->orderBy('timestamp','desc')
			->first();
	
	   return array('detail'=>$basedetail,'rtcm3'=>$rtcm3);//
    }
	
	public function getsatplots(Request $request)
    {
	   $id = $request->id;
	   $plots = [];	
	   
	   $basedetail = BaseStation::
	   		where('id',$id)
		 	->first();
		
//		$rtcm3 = CasterRequestRTCM3::
//			where('basestation_id',$id)
//			->orderBy('timestamp','desc')
//			->first();
		
		$sat = CasterRequestSatellite::
			where('basestation_id',$id)
			->orderBy('timestamp','desc')
			->first();
		
		$lat = $basedetail->latitude;//41.702; //BS Lat get
		$long = $basedetail->longitude;//-76.014; //BS Long get
		
		$satdata = $this->getsatsdata($sat->g_sats,$lat,$long);
		
		return array('plots'=>$satdata['plots'],'sats'=>$satdata['sats']);	
	}
	
	public function getsatsdata($sats,$lat,$long){
		
		$plots = [];
		$satlist = [];
		
		$sats = explode(',',$sats);
		
		foreach($sats as $sat){
			$satprn = substr($sat,1);
			$satid = CasterSatellitePRN::
			where('prn',$satprn)
			->first();
			$satlist[] = $satid;
		}
		
		//return array('plots'=>$plots,'sats'=>$satlist);
		
		foreach($satlist as $satid){
			
			if(isset($satid->title)){
			
			$title = $satid->title;
			
			if(isset($satid->satid)){$satid = $satid->satid;} 
			$plot = $this->getn2yo($title,$satid,$lat,$long);
			
		
			$angle = $plot['azimuth'];
			$elevation = $plot['elevation'];
			$name = $plot['satname'];
			//if($elevation > 0){
			$calc = $this->xycalc($angle,$elevation);
			
			$plots[] = array('name'=>$name,'x'=>$calc[0],'y'=>$calc[1],'azimuth'=>$angle,'elevation'=>$elevation);	
			
			}
			
		}
		
		return array('plots'=>$plots,'sats'=>$satlist);
	 	
	  // return array('plots'=>$plots,'sats'=>CasterRequestSatelliteResource::collection($sats));//
    }
	
	public function xycalc($angle,$elevation){
		
		$xfactor = 1;
		$yfactor = 1;
		$x = 0;
		$y = 0;
		
		
		if($angle == 360){$y = $elevation; $x = 0;}
		if($angle == 90){$x = $elevation; $y = 0;}
		if($angle == 180){$y = 1 - $elevation; $x = 0;}
		if($angle == 270){$x = 1 - $elevation; $y = 0;}
		
		if($x == 0 && $y == 0){
			
			if($angle < 90){$xfactor = 1;$yfactor = -1;}
			if($angle > 90 && $angle < 180){$angle = $angle - 90; $xfactor = 1;$yfactor = 1;}
			if($angle > 180 && $angle < 270){$angle = $angle - 180; $xfactor = -1;$yfactor = 1;}
			if($angle > 270 && $angle < 360){$angle = $angle - 270; $xfactor = -1;$yfactor = -1;}
			
			$anglerad = deg2rad($angle);
			$x = sin($anglerad) * $xfactor * ($elevation/28);
			$y = cos($anglerad) * $yfactor * ($elevation/28);
			}
		
		return array($x,$y);
	}
	
	
    public function store(Request $request)
    {
		
        $base = $request->isMethod('put') ? BaseStation::findorfail($request->value) : new Basestation;
		$base->id = $request->value;
		$base->title = $request->text;
		$base->client = $request->client;
		$base->save();
		return new BaseStationResource($base);
	}

   
    public function show($id)
    {
        $base = BaseStation::findorfail($id);
		return new BaseStationResource($base);
    }


    public function destroy($id)
    {
        $base = BaseStation::findorfail($id);
		if($base->delete()){
			return new BaseStationResource($base);
		}
    }
	
	public function getn2yo($satname,$id,$lat,$long){
		
		
		
		$link = 'https://api.n2yo.com/rest/v1/satellite/positions/'.$id.'/'.$lat.'/'.$long.'/0/1/&apiKey=FG2HXP-7EDWMH-YD2BE3-4TES';
		//$link = 'http://charlie:1234@nickabbeyservices.co.uk:2101/admin?mode=rehash';
		
		$response = Http::withHeaders([			//	Get data per device from Flespi
    				'accept' => 'application/json',
					//'Authorization' => 'FlespiToken XyeV5xS72tgx3e5XjuDeS9evvxKWJdZxz1guoXregvA7xvUGNmJvlLOE8pqHqf2P'
					])
  					->get($link);
		
		if(isset($response['info'])){
		//$satname = $response['info']['satname'];
		$azimuth = $response['positions'][0]['azimuth'];
		$elevation = $response['positions'][0]['elevation'];
		} else {
			$satname = 'none';
			$azimuth = 0;
			$elevation = 0;
		}
		return array('id'=>$id,'satname'=>$satname,'azimuth'=>$azimuth,'elevation'=>$elevation);
		
	}
}
