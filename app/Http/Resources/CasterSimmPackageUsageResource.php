<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterSimmPackageUsageResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'id' 		=> $this->id,
			'package'	=> $this->package,
		    'usage' 	=> $this->usage_raw,
		    'usage_date' => $this->usage_date 
		];
    }
}
