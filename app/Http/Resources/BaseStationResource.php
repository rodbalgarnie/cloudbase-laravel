<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseStationResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
			'text' 			=> $this->title,
		    'mount' 		=> $this->mount,
		    'status'		=> $this->status,
		    'gsats'			=> $this->last_gsats,
		    'rsats'			=> $this->last_rsats,
		    'mapstatus'		=> $this->getmapstatus($this->status),
		    'lastrtcm3'		=> date('d-m-Y H:i:s',strtotime($this->lastrtcm3)),
		    'statustext'	=> $this->getBSStatus['message'],
		    'statuscolor'	=> $this->getBSStatus['colour'],
		    'latitude'		=> $this->latitude,
		    'longitude'		=> $this->longitude,
		    'address1'		=> $this->address1,
		    'address2'		=> $this->address2,
		    'address3'		=> $this->address3,
		    'towncity'		=> $this->towncity,
		    'county'		=> $this->county,
		    'postcode'		=> $this->postcode,
		   
		];
    }
}
