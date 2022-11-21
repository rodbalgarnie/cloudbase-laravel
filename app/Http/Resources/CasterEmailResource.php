<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterEmailResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		    'title'			=> $this->title,
		   	'text' 			=> $this->text,
		    'group'			=> $this->group 
		];
    }
}
