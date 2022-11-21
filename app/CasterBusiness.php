<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CasterBusiness extends Model
	
{
 	protected $table = "caster_business";
	
	public function dealers()
	{	
		return $this->hasMany('App\CasterBusinessDealer', 'id', 'dealer')->withDefault([ 'title' => '-']);	
   	}
	
	public function users()
	{	
		return $this->hasMany('App\User', 'business', 'id')->where('role',5)->where('archive', 0); 
	}
	
}