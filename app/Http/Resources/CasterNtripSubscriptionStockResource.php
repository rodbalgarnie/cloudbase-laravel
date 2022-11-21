<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterNtripSubscriptionStockResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id, 
		    'ntripsub'  	=> $this->ntripsub,
		   	'subscription'  => $this->subscription, //CasterSubscriptionResource::collection($this->subscription),
			'dealer' 		=> $this->dealer,
		    'company' 		=> $this->company,
		    'rover'			=> $this->rover,
		    'user'			=> UserResource::collection($this->users), 
		    'status'		=> $this->status,
		    'statustext'	=> $this->getstatus($this->status),
		    'type'			=> $this->type,
		    'startdate'		=> date('d M y H:i',strtotime($this->startdate)),
		    'enddate'		=> date('d M y H:i',strtotime($this->enddate)),
		    'created'		=> date('d-M-Y',strtotime($this->created_at))
		   
		];
    }
}
