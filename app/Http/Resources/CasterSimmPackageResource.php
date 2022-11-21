<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterSimmPackageResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'id' 		=> $this->id,
			'value' 	=> $this->id,
		    'text' 		=> $this->text,
		    'package_id'	=> $this->package_id,
		    'data_allowance'	=> $this->data_allowance,
		   	'data_used' 		=> $this->data_used,
		    'data_usage' 		=> $this->data_usage,
		    'data_usage_date' 	=> $this->data_usage_date
		];
    }
}
