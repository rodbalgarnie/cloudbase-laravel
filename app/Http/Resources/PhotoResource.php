<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhotoResource extends JsonResource 
{
   
   public function toArray($request)
    {
       return [
			'value' 			=> $this->id,
		    'filename'			=> $this->filename,
			'parent' 			=> $this->parent,
		    'type'				=> $this->type,
		    'main'				=> $this->main
		];
    }
}
