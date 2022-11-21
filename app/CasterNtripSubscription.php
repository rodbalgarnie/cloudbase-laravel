<?php
// Version 210922/1815
namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class CasterNtripSubscription extends Model implements Auditable
	
{
 	protected $table = "caster_ntrip_subscriptions";
	use AuditableTrait;
	
	public function subscription()
    {
      return $this->hasMany('App\CasterSubscription', 'type', 'type'); 
	}
	
	public function renewsubscription()
    {
      return $this->hasMany('App\CasterNtripSubscriptionStock', 'sub_id', 'id')->where('status',2); 
	}
	
	public function users()
    {
      return $this->hasMany('App\User', 'id', 'user'); 
	}
	
	public function subscriptiontitle()
    {
      return $this->hasOne('App\CasterSubscription', 'type', 'type')->withDefault([ 'id' => 0,'title' => '-'	]);  
	}
	
	
	public function rovers()
    {
      return $this->hasOne('App\Rover', 'id', 'rover')->withDefault([ 'id' => 0,'title' => '-'	]);  
	}
	
	public function companies()
    {
      return $this->hasOne('App\CasterCompany', 'id', 'company')->withDefault([ 'id' => 0,'title' => '-'	]); 
	}
	
	public function dealers()
    {
      return $this->hasOne('App\CasterDealer', 'id', 'dealer')->withDefault([ 'id' => 0,'title' => '-'	]);	  
	}
	
	public function simms()
    {
      return $this->hasOne('App\CasterSimm', 'rover', 'rover')->withDefault([ 'id' => 0,'number' => 'Not Required'	]);	 
	}
	
	public function renewsent(){
		if($this->renewsent == 0){return 'No';} else return 'Yes';
	}
	
	public function getstatus($status){
		$text = "??";
		switch ($status){
			case 1:
				$text="Stock";
				break;
			case 2:
				$text="Pending";
				break;
			case 3:
				$text="Active";
				break;
			case 4:
				$text="Expired";
				break;	
			case 5:
				$text="Suspended";
				break;
			case 6:
				$text="Cancelled";
				break;		
		}
		
		return $text;
	}
	
	public function expirycolor(){
		$color = '#53c16b';
		if ($this->thirtyday == 1){$color = '#FF8A8A';}
		if ($this->sevenday == 1){$color = '#FF5C5C';}
		if ($this->oneday == 1){$color = '#FF2E2E';}
		if ($this->oneday == 1 && $this->active == 0){$color = '#FF0000';}	// Expired
		return $color;
	}
	
	public function expirydays(){
//		$days = 999;
//		if ($this->thirtyday == 1){$days = 30;return $days;}
//		if ($this->sevenday == 1){$days = 7;return $days;}
//		if ($this->oneday == 1){$days = 1;return $days;}
//		if ($this->oneday == 1 && $this->active == 0){$days = 0;return $days;}	// Expired
		$now =strtotime(date('Y-m-d H:i:s'));
		$end = strtotime($this->enddate);
		$days = round(($end - $now) /(60 * 60 * 24));
		if($days < 0){$days = 0;}
		return $days;
	}
	
}