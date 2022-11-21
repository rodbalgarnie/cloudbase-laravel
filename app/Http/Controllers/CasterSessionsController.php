<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\CasterSession;
use App\Rover;
use App\Http\Controllers\Controller; 
use App\Http\Resources\CasterSessionLongResource as CasterSessionLongResource;


class CasterSessionsController extends Controller
{

	public function fixsessioncount(){
	$sessions = CasterSession::get();
		foreach($sessions as $session){
			$numggas = $session->num_fix+$session->num_float+$session->num_standalone+$session->num_dgps;
			if($numggas > 0){$quality = $session->num_fix/$numggas * 100;} else $quality = 0;
			$session->num_ggas = $numggas;
			$session->quality = $quality;
			$session->save();
		}
	}	
	
   public function roversessiondata(Request $request)
    {
	   $rover = $request->rover;
	   $type = $request->type; // 1 - number connectons 2- connection time
	   $year = '2022';
	   $monthloop = 1;
	   $data = [];
	   $labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
	   
	   while($monthloop < 13){
		   
		   $datesearch = $year.'-'.str_pad($monthloop, 2, '0', STR_PAD_LEFT);
		   
	
		   if($type == 1){	
			   $count = CasterSession::
					where('rover',$rover)
					->where('date_time','LIKE',$datesearch.'%')	
					->count();
			   $data[] =  $count;
			   $label = '# connections';
			   }
		   
		   	if($type == 2){	
				$time = 0;	
			   	$sessions = CasterSession::
					where('rover',$rover)
					->where('date_time','LIKE',$datesearch.'%')	
					->get();
				
				foreach($sessions as $session){
					$time = $time + $session['connection_time'];
				}
				
				if($time !== 0){$time = round($time/3600);}
				$data[] =  $time;
				$label = '# hours';
				
			   }

			

			$monthloop++;
		   }
	   
	   $ds = array('label'=>$label,'color'=>'#53c16b','data'=>$data);	
	   
		return array('labels'=>$labels,'datasets'=>$ds);
	   
   	}
	
	public function networkloginsdata(Request $request){
		
		$dealer = $request->dealer;
		$start = $request->start.' 00:00:00';
		$end = $request->end.' 23:59:59';
		$period = $request->period;
		
		
		$rovers= Rover::select('id')
				->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
				})
				->get();
		
		$sessions = CasterSession::
			whereIn('rover',$rovers)
			->whereBetween('date_time',[$start,$end])	
			->get();	
		
		return array('Logins'=>CasterSessionLongResource::collection($sessions));//
		
	}
	
	
}
	
