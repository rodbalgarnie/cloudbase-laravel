<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CasterLog extends Model
	
{
 	protected $table = "caster_data_log";
	
	public function rovers(){
		return $this->hasOne('App\Rover', 'id', 'rover')->withDefault([ 'title' => '-'	]);	
   	}
	
	public function messages(){
		return $this->hasOne('App\CasterMessage', 'type', 'type')
        ->where('code', $this->code)
		->withDefault([ 'message' => '-','colour' => '-' ]);		
  	}
	
  
}
