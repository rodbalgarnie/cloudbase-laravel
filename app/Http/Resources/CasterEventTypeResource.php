<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterEventTypeResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		   	'text' 			=> $this->text,
		    'group'			=> $this->group
		];
    }
}
