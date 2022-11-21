<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CasterBusinessDealer extends Model
	
{
 	protected $table = "caster_business_dealers";
	
	public function businesses()
	{	
		return $this->hasOne('App\CasterBusiness', 'id', 'business');//->withDefault([ 'title' => '-']);	
   	} 
	
	public function dealers()
	{	
		return $this->hasOne('App\CasterDealer', 'id', 'dealer');//->withDefault([ 'title' => '-']);	
   	}  
	
}