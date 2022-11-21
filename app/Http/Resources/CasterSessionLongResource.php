<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterSessionLongResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		    'datetime'		=> $this->date_time,
		    'basestationdetail' => $this->basestations,
		    'useragent'		=> $this->user_agent,
		    'session_id'	=> $this->session_id,
		    'quality'		=> $this->quality,
		    'ggas'			=> $this->num_ggas,
		    'connection_time' => $this->connection_time,
		    'time_to_fix'		=> $this->time_to_fix,
		    'bytessent'		=> $this->bytes_sent,
		   	'dealer' 		=> $this->dealer,
		    'rover'			=> RoverResourceDetails::collection($this->rovers)
		   
		];
    }
}
