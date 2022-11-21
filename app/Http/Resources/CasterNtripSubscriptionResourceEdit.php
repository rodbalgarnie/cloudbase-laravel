<?php
// Version 260922/1420
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterNtripSubscriptionResourceEdit extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		    'username'		=> $this->rovers['username'], 
		    'password'		=> $this->rovers['password'],
		    'connection'	=> $this->rovers['connection'],
		   	'port'			=> $this->rovers['port'],
		    'purchase_order'=> $this->purchase_order,
		    'simm'			=> $this->simms['id'],
		    'simmnum'		=> $this->simms['number'],
		    'nosimm'		=> $this->nosimm,
		   	'subscription'  => $this->subscription, //CasterSubscriptionResource::collection($this->subscription),
			'dealer' 		=> $this->dealer,
		    'company' 		=> $this->company,
		    'rover' 		=> $this->rover,
		    'user'			=> $this->user,
		    'status'		=> $this->status,
		    'statustext'	=> $this->getstatus($this->status),
		    'active'		=> $this->active,
		    'type'			=> $this->type,
		    'subid'			=> $this->id,
		    'stocksub'		=> $this->stocksub,
		    'startdate'		=> date('d-m-Y H:i:s',strtotime($this->startdate)),
		    'enddate'		=> date('d-m-Y H:i:s',strtotime($this->enddate)),
		    'firstactivation'		=> date('d-m-Y H:i:s',strtotime($this->first_activation)),
		    'autorenew'		=> $this->autorenew,
		    'renew_once'	=> $this->renew_once,
		    'renewsent'		=> $this->renewsent,
		    'renew_sub'		=> CasterNtripSubscriptionStockRenewalResource::collection($this->renewsubscription),
		    'consumption'	=> $this->consumption,
		   	'created'		=> date('d-M-Y',strtotime($this->created_at))
		   
		];
    }
}
