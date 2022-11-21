<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterRequestSatelliteResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
		'id' 			=> $this->id,
		'basestation_id'	=> $this->basestation_id,   
		'basestation'	=> $this->basestations['title'],
		'timestamp'		=> $this->timestamp,
		'gsats'			=> $this->g_sats,
		'rsats'			=> $this->r_sats,
		'esats'			=> $this->e_sats,
		'csats'			=> $this->c_sats,   
		'gsats_count'	=> $this->g_count,
		'rsats_count'	=> $this->r_count,
		'esats_count'	=> $this->e_count,
		'csats_count'	=> $this->c_count,
		'session'		=> $this->session_id   
		];
    }
}
