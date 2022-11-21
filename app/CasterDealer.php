<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CasterDealer extends Model
	
{
 	protected $table = "caster_dealer";
	
	public function companies()
    {
      return $this->hasMany('App\CasterCompany', 'dealer', 'id'); 
	}
	
	
	
	public function users()
	{	
		return $this->hasMany('App\User', 'dealer', 'id')->where('role',10)->where('archive', 0); 
	}
	
	public function userscount($type){
		$users = User::where('dealer',$this->id)->where('role',$type)->get();
		return count($users);
	}
	
	public function subcount(){
		$subs = CasterNtripSubscription::where('dealer',$this->id)->get();
		return count($subs);
	}
	
	public function companycount(){
		$companies = CasterCompany::where('dealer',$this->id)->get();
		return count($companies);
	}
	
	public function rovercount(){
		$rovers = Rover::where('dealer',$this->id)->get();
		return count($rovers);
	}
	
	public function setlogo($logo){
		if($logo == ''){$file = '/images/logos/blank.jpg';}
			else $file = '/images/logos/'.$logo;
		return $file;
	}
	
}