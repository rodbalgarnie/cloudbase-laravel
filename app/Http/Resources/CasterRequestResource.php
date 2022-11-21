<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterRequestResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
		'id' 			=> $this->id,
		'string' 		=> $this->splitgga($this->GGA_string),
		'session'		=> $this->session_id,
		'lastconnect'	=> $this->last_connect,
		'timedate'		=> $this->timedate,
		'rover'			=> $this->rovers['title'],
		'basestation'	=> $this->basestations['title'],   
		'status'		=> $this->rtk_fix_status,
		'statustext'	=> $this->getRTKStatus['message'],   
		'satelites'		=> $this->num_sateliites,
		'hdop'			=> $this->hdop,	   
		'latitude'		=> round($this->latitude,8),
		'longitude'		=> round($this->longitude,8), 
		'distance'		=> $this->distance,
		'altitude'		=> $this->altitude,   
		'age'			=> $this->data_age,	   
		];
    }
}
