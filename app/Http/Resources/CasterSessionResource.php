<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterSessionResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		    'datetime'		=> $this->date_time,
		   	'dealer' 		=> $this->dealer,
		    'rover'			=> $this->rover 
		   
		];
    }
}
