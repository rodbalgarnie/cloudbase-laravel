<?php
// Version 200722 
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterNtripSubscriptionStockRenewalResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		    //'ntripsub'  	=> $this->ntripsub,
		   	'subscription'  => $this->subscription, //CasterSubscriptionResource::collection($this->subscription),
			'dealer' 		=> $this->dealer,
		    'company' 		=> $this->company,
		    'rover'			=> $this->rover,
		    'status'		=> $this->status,
		    'statustext'	=> $this->getstatus($this->status),
		    'type'			=> $this->type,
		    'purchase_order'=> $this->purchase_order,
		    'startdate'		=> date('d-m-Y H:i:s',strtotime($this->startdate)),
		    'enddate'		=> date('d-m-Y H:i:s',strtotime($this->enddate)),
		    'created'		=> date('d-M-Y',strtotime($this->created_at))
		   
		];
    }
}
