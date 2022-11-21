<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CasterRequestRTCM3 extends Model
	
{
 	protected $table = "caster_data_rtcm3";
	
	public function basestations()
    {
      return $this->hasOne('App\BaseStation', 'id', 'basestation_id'); 
	}
	
	public function sats(){
		$sats = 'G:'.$this->num_satellites_g.' R:'.$this->num_satellites_r.' E:'.$this->num_satellites_e.' C:'.$this->num_satellites_c;
		return $sats;
	}
	
	public function satcount(){
		if($this->message == 1004){return $this->num_satellites_g;}
		if($this->message == 1012){return $this->num_satellites_r;}
		return 0;
	}
	
	public function getstatuscolor(){
		
		if($this->message == 1004){
			if($this->num_satellites_g < 5 ){return '#FF0000';}
			return '#5bc89c';
			}
		
		if($this->message == 1012){
			if($this->num_satellites_r < 5 ){return '#FF0000';}
			return '#5bc89c';
			}
		
		return '#666666';
		
	}
	
	public function getmessage(){
		if($this->message == '1004'){
			return "1004/GPS";
			}
		
		if($this->message == '1012'){
			return "1012/GLONASS";
			}
		
		return '';
	}
}
