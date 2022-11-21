<?php

namespace App;

use Illuminate\Database\Eloquent\Model; 

class CompanyMachine extends Model
{
	protected $table = "caster_company_machines";
	
	
	public function makes()
    {
      return $this->hasOne('App\MachineMaker', 'id', 'make')->withDefault([
        'title' => 'undefined'
    	]);  
	}
	
	public function models()
    {
      return $this->hasOne('App\MachineModel', 'id', 'model')->withDefault([
        'title' => 'undefined'
    	]);  
	}
	
	public function types()
    {
      return $this->hasOne('App\MachineType', 'id', 'type')->withDefault([
        'title' => 'undefined'
    	]);  
	}
	
	public function rovers()
    {
      return $this->hasOne('App\Rover', 'id', 'rover')->withDefault([
        'title' => '-'
    	]);  
	}
	
	public function subs()
    {
      return $this->hasMany('App\CasterNtripSubscription', 'rover', 'rover');
//		->withDefault([
//        'title' => '-'
//    	]);  
	}
	
	public function companies()
    {
      return $this->hasOne('App\CasterCompany', 'id', 'company')->withDefault([        'title' => '-'   	]);  
	}
	
//	public function clients()
//    {
//      return $this->hasOne('App\Client', 'id', 'client'); 
//	}
	
	public function notes()
    {
      return $this->hasMany('App\Note', 'parent', 'id')->where('type',3); // 3 Client Machine Note Type
	}
	
	public function photos()
    {
      return $this->hasMany('App\Photo', 'parent', 'id')->where('type',3); // 3 Client Machine Photo Type
	}

	
	public function mainphoto()
    {
		$photo = Photo::where('parent',$this->id)->where('type',3)->where('main',1)->first();
		if($photo){ return $photo->filename;}
		
		$photo = Photo::where('parent',$this->machine)->where('type',4)->where('main',1)->first();
		if($photo){ return $photo->filename;}
		
		return 'noimage.jpg';
   
	}
	
}
