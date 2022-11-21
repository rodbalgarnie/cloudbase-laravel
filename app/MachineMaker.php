<?php

namespace App;

use Illuminate\Database\Eloquent\Model; 

class MachineMaker extends Model
{
	protected $table = "machine_makers";
	
	public function types()
    {
      return $this->hasMany('App\MachineModel', 'id', 'model'); 
	}
	

	
}
