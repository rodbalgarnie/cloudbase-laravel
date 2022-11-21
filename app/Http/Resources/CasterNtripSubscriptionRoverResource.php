<?php
// Version 230922/1600
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterNtripSubscriptionRoverResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		   	'subscription'  => CasterSubscriptionResource::collection($this->subscription),
		    'status'		=> $this->getstatus($this->status), 
		  	'active'		=> $this->active,
		    'type'			=> $this->type,
		    'startdate'		=> strtotime($this->startdate),
		    'enddate'		=> strtotime($this->enddate),
		    'firstactivation'	=> strtotime($this->first_activation),
		    'autorenew'		=> $this->autorenew,
		    'renew_once'	=> $this->renew_once,
		    'consumption'	=> $this->consumption,
		   	'created'		=> date('d-M-Y',strtotime($this->created_at))
		   
		];
    }
}
