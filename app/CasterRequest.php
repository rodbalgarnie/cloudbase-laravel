<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class CasterRequest extends Model
	
	
{
 	protected $table = "caster_data_gpgga"; 
	use \Staudenmeir\EloquentEagerLimit\HasEagerLimit;
	
	
	public function rovers()
    {
      return $this->hasOne('App\Rover', 'id', 'rover_id')->withDefault([ 'title' => '-'	]);	
	}
	
	public function basestations()
    {
      return $this->hasOne('App\BaseStation', 'id', 'basestationid')->withDefault([ 'title' => '-'	]);	
	}
	
	public function getRTKStatus(){
		 return $this->hasOne('App\RTKStatus', 'code', 'rtk_fix_status'); 
	}
	
	
	
	public function splitgga($str){
		$gga = [];
		
		$pos1 = strpos($str,"GPGGA");
		$pos2 = strpos($str,"GPGGA", $pos1 + strlen("GPGGA")) - 3;
		$str = substr($str,0,$pos2);
		return $str;//[0];
	}
	
	//->withDefault([ 'text' => '-'	]);  
	
//	 public function casterrequest()
//    {
//		//return $this->belongsTo('App\Rover', 'id', 'roverid' );  
//        return $this->belongsTo(App\Rover::class);
//    }
//	
	
}