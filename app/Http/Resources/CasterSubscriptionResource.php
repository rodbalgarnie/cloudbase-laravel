<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterSubscriptionResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->type,
			'text' 			=> $this->title,
		    'price' 		=> $this->price,
		    'type'			=> $this->type,
		    'years'			=> $this->years,
		    'months'		=> $this->months,
		    'days'			=> $this->days
		];
    }
}
