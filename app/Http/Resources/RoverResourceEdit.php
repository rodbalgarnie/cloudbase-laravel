<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoverResourceEdit extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
			'text' 			=> $this->title,
		    'dealer'		=> $this->dealer,
		    'company'		=> $this->company,
		    'machine'		=> $this->machine,
		    'sub'			=> CasterNtripSubscriptionResourceRover::collection($this->subs),
		    'machinetext'	=> $this->machines['text'],
		    'simm'			=> $this->simm,
		    'ccid'			=> $this->simms['number'],
		   	'username'		=> $this->username,
		    'password'		=> $this->password,
		    'created'		=> date('d-m-y H:i',strtotime($this->updated_at))
		
		];
    }
}
