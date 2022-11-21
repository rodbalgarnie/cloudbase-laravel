<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoverResourceMap extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		    'text' 			=> $this->title,
			'name'	 		=> $this->title,
		    'status'		=> $this->rtk_status,
		    'statustext'	=> $this->getRTKStatus['message'],
		    'color'			=> $this->getRTKStatus['colour'],
		   	'lastgga'		=> $this->lastgga($this->last_mesg),
		    'lastsession'	=> $this->lastsession($this->id),
		    'fixtime'		=> $this->fix_time,
		    'dealer'		=> $this->dealer,
		    'company'		=> $this->company,
		    'machine'		=> $this->machine,
		    'rtk_status'	=> $this->rtk_status,
		    'lastlog'		=> $this->lastlog($this->last_log),
		    'lastlogid'		=> $this->last_log,
		    'lastconnect'	=> $this->last_connect,
		   	'lastconnect2'	=> date('d-m-y H:i:s',$this->last_connect),
		    'fixtime'		=> $this->fix_time,
		   	'username'		=> $this->username,
		    'password'		=> $this->password,
		    
		
		];
    }
}
