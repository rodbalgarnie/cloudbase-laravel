<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterSessionMapResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		    'rover'			=> $this->rovers['title'],
		   	'lat'			=> $this->last_lat,
		    'long'			=> $this->last_long
		   
		];
    }
}
