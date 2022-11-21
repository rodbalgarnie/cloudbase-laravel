<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CasterRequestSatellite extends Model
	
{
 	protected $table = "caster_data_satellites"; 
	
	public function basestations()
    {
      return $this->hasOne('App\BaseStation', 'id', 'basestation_id'); 
	}
	
	public function sats()
    {
      return $this->hasMany('App\CasterSatellitePRN', 'prn', 'ident'); 
	}
	
}
