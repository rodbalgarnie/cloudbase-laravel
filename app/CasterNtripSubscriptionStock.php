<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;


class CasterNtripSubscriptionStock extends Model implements Auditable
	
{
 	protected $table = "caster_ntrip_subscriptions_stock"; 
	use AuditableTrait;
	
	public function subscription()
    {
      return $this->hasMany('App\CasterSubscription', 'type', 'type'); 
	}
	
	public function ntripsub()
    {
      return $this->hasMany('App\CasterNtripSubscription', 'id', 'sub_id'); 
	}
	
	public function users()
    {
      return $this->hasMany('App\User', 'id', 'user'); 
	}
	
	
	public function rovers()
    {
      return $this->hasOne('App\Rover', 'id', 'rover'); 
	}
	
	public function companies()
    {
      return $this->hasMany('App\CasterCompany', 'id', 'company'); 
	}
	
	public function dealers()
    {
      return $this->hasMany('App\CasterDealer', 'id', 'dealer'); 
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
	
	
}