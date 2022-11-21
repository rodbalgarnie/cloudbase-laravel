<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterBusinessDealerResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'id' 			=> $this->id, 
			'businessid' 	=> $this->business,
		    'business'		=> $this->businesses,
		   	'dealerid' 		=> $this->dealer,
		    'dealer'		=> $this->dealers
		   
		   
		];
    }
}
