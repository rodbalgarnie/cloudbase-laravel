<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CasterDealerDepot extends Model
	
{
 	protected $table = "caster_dealer_depots";
	
	public function companies()
    {
      return $this->hasMany('App\CasterCompany', 'dealer', 'id'); 
	}
	
	
	public function subscriptions()
    {
      return $this->hasMany('App\CasterNtripSubscription', 'dealer', 'id'); 
	}
	
	public function dealers()
	{	
		return $this->belongsTo('App\CasterDealer', 'id', 'maindealer');
		//return $this->hasMany('App\CasterDealer', 'id', 'maindealer');
	}
	
}