<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MachineMakerResource extends JsonResource 
{
   
   public function toArray($request)
    {
       return [
			'value' 	=> $this->id,
			'text' 		=> $this->title
		   
		];
    }
}
