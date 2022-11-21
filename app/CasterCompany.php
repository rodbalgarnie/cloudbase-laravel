<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CasterCompany extends Model
	
{
 	protected $table = "caster_companies";
	
	public function users()
    {
    return $this->hasMany('App\User', 'company', 'id')->where('role',20)->where('archive', 0); 
	}
	
	
	public function dealers()
	{	
		return $this->hasOne('App\CasterDealer', 'id', 'dealer')->withDefault([ 'title' => '-']);	
   	}
	
	public function subs()
	{	
		return $this->hasMany('App\CasterNtripSubscription', 'company', 'id');	
   	}
	
	public function machines(){
		 return $this->hasMany('App\CompanyMachine', 'company', 'id'); 
		 return $this->hasMany('App\CompanyMachine', 'company', 'id'); 
	}
	
	public function userscount($type){
		$users = User::where('company',$this->id)->where('role',$type)->get();
		return count($users);
	}
	
	public function subcount(){
		$subs = CasterNtripSubscription::where('dealer',$this->id)->get();
		return count($subs);
	}
	
	public function machinecount(){
		$machines = CompanyMachine::where('company',$this->id)->get();
		return count($machines);
	}
	
	public function rovercount(){
		$rovers = Rover::where('company',$this->id)->get();
		return count($rovers);
	}
	
	public function company()
  {
    return $this->belongsTo(Company::class);
  }
	
}