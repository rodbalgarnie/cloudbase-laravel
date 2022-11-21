<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterLogResource extends JsonResource 
{
   
   public function toArray($request)
    {
       return [
		'id' 			=> $this->id,
		'rover' 		=> $this->rovers['title'],   
		'message' 		=> $this->messages['message'],
		'colour' 		=> $this->messages['colour'],   
		'type'			=> $this->type,   
		'code'			=> $this->code,
		'session'		=> $this->session_id,   
		'timestamp'		=> $this->timestamp,
		'timedate'		=> date('d-m-Y H:i:s',$this->timestamp)   
		];
    }
}