<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dept extends Model
{
	protected $table="depts";
	
    public function cccodes()
    {
      return $this->hasOne('App\CCCode', 'code_dept', 'id'); 
	}
}
