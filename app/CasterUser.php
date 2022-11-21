<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CasterUser extends Model
	
{
 	protected $table = "caster_users";
	
	public function dealerdetail()
    {
      return $this->belongsTo('App\CasterDealer', 'dealer', 'id'); 
	}	
	
	public function companydetail()
    {
      return $this->belongsTo('App\CasterCompany', 'company', 'id'); 
	}	
	
//	public function subscriptiondetail()
//    {
//      return $this->belongsTo('App\CasterNtripSubscription', 'user', 'id'); 
//	}	
	
	
	public function subscriptions()
    {
      return $this->hasOne('App\CasterSubscription', 'id', 'subscription')->withDefault([
        'title' => 'Not Found'
    	]);   
	}
	
//	public function setrenew($id)
//    {
//		if($id == 0){return 'No';} else return 'Yes';
//	}
}