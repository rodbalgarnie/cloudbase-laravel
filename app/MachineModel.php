<?php

namespace App;

use Illuminate\Database\Eloquent\Model; 

class MachineModel extends Model
{
	protected $table = "machine_models";
	
	public function types()
    {
      return $this->hasOne('App\MachineType', 'id', 'type'); 
	}
	
	public function makers()
    {
      return $this->hasOne('App\MachineMaker', 'id', 'maker'); 
	}
	

	
}
