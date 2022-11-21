<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CasterSession extends Model
	
{
 	protected $table = "caster_sessions"; 
	
	public function basestations()
    {
      return $this->hasMany('App\BaseStation', 'id', 'basestation'); 
	}
	
	public function rovers()
    {
      return $this->hasMany('App\Rover', 'id', 'rover'); 
	}
	
	
	public function getconnection_time($time){
		$dtF = new \DateTime('@0');
    	$dtT = new \DateTime("@$time");
		return $dtF->diff($dtT)->format('%a Days %h Hours %i Mins %s Secs');
	}
	
}
