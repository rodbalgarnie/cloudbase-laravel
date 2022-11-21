<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterSimmStatusResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'id' 		=> $this->id,
			'value' 	=> $this->id,
		   	'text' 		=> $this->text,
		];
    }
}
