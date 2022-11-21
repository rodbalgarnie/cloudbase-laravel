<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoverResourceDetails extends JsonResource 
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
			'text' 			=> $this->title,
		    'dealer'		=> $this->dealers['title'],
		    'company'		=> $this->companys['title']
		  
		];
    }
}
