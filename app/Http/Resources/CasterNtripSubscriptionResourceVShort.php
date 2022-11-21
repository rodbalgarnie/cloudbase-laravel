<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterNtripSubscriptionResourceVShort extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		    'dealer'		=> $this->dealer,
		    'user'			=> $this->user,
		    'active'		=> $this->active,
		    'type'			=> $this->type,
		    'startdate'		=> date('d-m-Y H:i:s',strtotime($this->startdate)),
		    'enddate'		=> date('d-m-Y H:i:s',strtotime($this->enddate)),
		    'autorenew'		=> $this->autorenew,
		    'consumption'	=> $this->consumption,
		   	'created'		=> date('d-M-Y',strtotime($this->created_at))
		   
		];
    }
}
