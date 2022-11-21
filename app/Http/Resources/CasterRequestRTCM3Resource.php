<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterRequestRTCM3Resource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
		'id' 			=> $this->id,
		'basestation_id'	=> $this->basestation_id,   
		'basestation'	=> $this->basestations['title'],
		'timestamp'		=> $this->timestamp,
		'message'		=> $this->getmessage(),   
		'sats'			=> $this->sats(),
		'satcount'		=> $this->satcount(),   
//		'gsats'			=> $this->num_satellites_g,
//		'rsats'			=> $this->num_satellites_r,
//		'esats'			=> $this->num_satellites_e,
//		'csats'			=> $this->num_satellites_c,
		'session'		=> $this->session_id,
		'colour'		=> $this->getstatuscolor() 
		   
		];
    }
}
