<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CasterEmailsLog extends Model  
	
{
 	protected $table = "caster_emails_log";  
	
	public function resellers(){
		 return $this->hasOne('App\CasterBusiness', 'id', 'reseller')->withDefault([ 'title' => '-']); 
	}
	
	
	public function companies(){
		 return $this->hasOne('App\CasterCompany', 'id', 'company')->withDefault([ 'title' => '-']); 
	}
	
	public function dealers(){
		 return $this->hasOne('App\CasterDealer', 'id', 'dealer')->withDefault([ 'title' => '-']); 
	}
	
	
}