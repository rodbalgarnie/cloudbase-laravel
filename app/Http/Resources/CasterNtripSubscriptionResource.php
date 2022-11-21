<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterNtripSubscriptionResource extends JsonResource 
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		    'username'		=> $this->username, 
		    'password'		=> $this->password,
		   	'subscription'  => $this->subscription, //CasterSubscriptionResource::collection($this->subscription),
			'dealer' 		=> $this->dealer,
		    //'dealerdetail' => CasterDealerResource::collection($this->dealers),
		    'companydetail' => CasterCompanyResource::collection($this->companies),
		    'roverdetail'	=> $this->rovers,
		    'user'			=> UserResource::collection($this->users),
		    'status'		=> $this->status,
		    'statustext'	=> $this->getstatus($this->status),
		    'active'		=> $this->active,
		    'type'			=> $this->type,
		    'startdate'		=> date('d M y H:i',strtotime($this->startdate)),
		    'enddate'		=> date('d M y H:i',strtotime($this->enddate)),
		    'autorenew'		=> $this->autorenew,
		    'consumption'	=> $this->consumption,
		   	'created'		=> date('d-M-Y',strtotime($this->created_at))
		   
		];
    }
}
