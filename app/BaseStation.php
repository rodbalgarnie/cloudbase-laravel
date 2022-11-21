<?php

//Version 131022/1000

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class BaseStation extends Model implements Auditable
{
	protected $table = "caster_basestations";
	use \OwenIt\Auditing\Auditable;
	
	protected $auditExclude = [
        'last_log',
		'last_mesg',
		'lastrtcm3',
		'last_gsats',
		'last_rsats'
		 
    ];
  	
	public function getBSStatus(){
		 return $this->hasOne('App\BaseStationStatus', 'code', 'status'); 
	}
	
	public function getmapstatus($status){
		$mapstatus = 2;
		if($status == 0){$mapstatus = 1;}
		if($status == 1){$mapstatus = 4;}
		return $mapstatus;
	}
	
	
}
