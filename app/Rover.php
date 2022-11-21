<?php
// Version 290922/1700
namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Rover extends Model implements Auditable
{
	protected $table = "caster_rovers"; 
	use \Staudenmeir\EloquentEagerLimit\HasEagerLimit;
	use \OwenIt\Auditing\Auditable;
	
	 protected $auditExclude = [
        'last_log',
		'last_mesg',
		 
    ];
	
	public function base()
    {
      return $this->belongsTo('App\BaseStation', 'basestation', 'id' ); 
	}
	
	public function lastgga($id)
    {
      //return $this->hasOne('App\CasterRequest', 'id', 'last_mesg' ); 
		$gga = CasterRequest::where('id',$id)->first(); 	
      	return $gga;	
	}
	
	public function lastlog($id)
    {
	  $log = CasterLog::where('id',$id)->first(); 	
      return $log;
	}
	
	public function getRTKStatus(){
		 return $this->hasOne('App\RTKStatus', 'code', 'rtk_status')->withDefault([
        'title' => 'undefined'
    	]); ; 
	}
	
	public function dealers(){
		 return $this->hasOne('App\CasterDealer', 'id', 'dealer'); 
	}
	
	public function companys(){
		 return $this->hasOne('App\CasterCompany', 'id', 'company'); 
	}
	
	public function ggas(){
		 return $this->hasMany('App\CasterRequest', 'id', 'last_mesg');  
	}
	
	public function subscriptions(){
		 return $this->hasMany('App\CasterNtripSubscription', 'rover', 'id')->orderBy('id','desc'); 
	}
	
	public function simms(){
		 return $this->hasMany('App\CasterSimm', 'id', 'simm');//)->withDefault([ 'ccid' => '-']); 
	}
	
	public function simms2(){
		 return $this->hasOne('App\CasterSimm', 'id', 'simm')->withDefault([ 'ccid' => '-']); 
	}
	
	public function machines(){
		 return $this->hasMany('App\CompanyMachine', 'id', 'machine');//->withDefault([ 'text' => 'None']); 
	}
	
	public function subs(){
		 return $this->hasMany('App\CasterNtripSubscription', 'id', 'subscription'); 
	}
	
	
	
//	public function ggalogs(){
//		return $this->hasMany('App\CasterRequest', 'rover_id', 'id' ); 
//	}
	
	public function lastsession($id)
    {
      //return $this->hasOne('App\CasterRequest', 'id', 'last_mesg' ); 
		$session = CasterSession::
			where('rover',$id)
			->orderby('id', 'desc')
			->first();
		
      	return $session;	
	}
	
	public function getlastgga(){
		$this->hasOne('App\CasterRequest', 'rover_id', 'id')->orderBy('id','desc');  	
   	}
	
	public function getlastconnect($datetime){
		if($datetime > 0){
			return $datetime;
		} else return 0;
	}
	
	
	
}
