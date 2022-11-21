<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CasterSimm extends Model 
	
{
 	protected $table = "caster_simms";
	
	
	public function getstatus()
	{	
		return $this->hasOne('App\CasterSimmStatus', 'id', 'status')->withDefault([ 'text' => '-']);
	}
	
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
	
	public function simmusages()
	{	
		return $this->hasMany('App\CasterSimmUsage', 'connectionid', 'connectionid');//->withDefault([ 'title' => '-']);	
   	}
	
	public function simmpackages()
	{	
		return $this->hasOne('App\CasterSimmPackage', 'id', 'package_id');	
   	}
	
	
}