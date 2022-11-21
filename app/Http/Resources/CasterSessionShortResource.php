<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterSessionShortResource extends JsonResource 
{
   
   public function toArray($request)
    {
       return [
		'value' => $this->id,
		'text'	=> $this->session_id   
		];
    }
}