<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RTKStatusResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->code,
			'text' 			=> $this->message
		];
    }
}
