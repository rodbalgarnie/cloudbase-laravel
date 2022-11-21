<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterSatelliteResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
		'id' 			=> $this->id,
		'prn'			=> $this->prn,
		'title'			=> $this->title   
		];
    }
}
