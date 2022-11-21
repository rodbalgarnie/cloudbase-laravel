<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterNtripSubscriptionResourceStock2 extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		   	'text'			=> $this->subscription[0]['title'],
		    'id'			=> $this->subscription[0]['id'],
		    'type'			=> $this->subscription[0]['type'],
		    'years'			=> $this->subscription[0]['years'],
		    'months'		=> $this->subscription[0]['months'],
		    'days'			=> $this->subscription[0]['days'],
		    
		];
    }
}
