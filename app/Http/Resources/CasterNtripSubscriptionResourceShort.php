<?php
//<!-- Version 20722/0800 -->
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterNtripSubscriptionResourceShort extends JsonResource 
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		    'username'		=> $this->username,
		    'password'		=> $this->password,
		    'nosimm'		=> $this->nosimm,
		    'po'			=> $this->purchase_order,
		   	'subscription'  => $this->subscriptiontitle['title'], //CasterSubscriptionResource::collection($this->subscription),
			'dealer' 		=> $this->dealer,
		    'dealerdetail' =>  $this->dealers['title'],
		    'companydetail' => $this->companies['title'],
		    'roverdetail'	=> $this->rovers['title'],
		    'status'		=> $this->status,
		    'statustext'	=> $this->getstatus($this->status),
		    'active'		=> $this->active,
		    'type'			=> $this->type,
		    'startdate'		=> date('d M y H:i',strtotime($this->startdate)),
		    'enddate'		=> date('d M y H:i',strtotime($this->enddate)),
		    'autorenew'		=> $this->autorenew,
		    'renewsent'		=> $this->renewsent(),
		    'consumption'	=> $this->consumption,
		    'color'			=> $this->expirycolor(),
		    'days'			=> $this->expirydays(),
		];
    }
}
