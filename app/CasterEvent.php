<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CasterEvent extends Model  
	
{
 	protected $table = "caster_event_log";
	
	
	public function dealers()
	{	
		return $this->hasOne('App\CasterDealer', 'id', 'dealer')->withDefault([ 'title' => '-']);	
   	}
	
	public function companys()
	{	
		return $this->hasOne('App\CasterCompany', 'id', 'company')->withDefault([ 'title' => '-']);	
   	}
	
	public function rovers()
	{	
		return $this->hasOne('App\Rover', 'id', 'rover')->withDefault([ 'title' => '-']);	
   	}
	
	public function users()
	{	
		return $this->hasOne('App\User', 'id', 'user')->withDefault([ 'title' => '-']);	
   	}
	
	public function types()
	{	
		return $this->hasOne('App\CasterEventType', 'id', 'type')->withDefault([ 'title' => '-']);	
   	}
	
	
}