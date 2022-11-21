<?php
// Version 230922/1600
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoverResourceUsers extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
			'text' 			=> $this->title,
		    'dealer'		=> $this->dealer,
		    'company'		=> $this->company,
		    'machine'		=> CompanyMachineResource::collection($this->machines),
		    'simm'			=> CasterSimmResource::collection($this->simms),
		    'dealerdetail'		=> $this->dealers,
		    'companydetail'		=> $this->companys,
		    'subscription'	=> $this->subscription,
		    'subscriptiondetail' => CasterNtripSubscriptionRoverResource::collection($this->subscriptions),
		    'rtk_status'	=> $this->rtk_status,
		    'color'			=> $this->getRTKStatus['colour'],
		   	'lastsession'	=> $this->lastsession($this->id),
		   	'statustext'	=> $this->getRTKStatus['message'],
		    'lastconnect'	=> $this->last_connect,
		   	'lastconnect2'	=> $this->getlastconnect($this->last_connect),
		    'username'		=> $this->username,
		    'password'		=> $this->password,
		    'connection'	=> $this->connection,
		    'port'			=> $this->port,
		    'created'		=> date('d-m-y H:i',strtotime($this->updated_at)) 
		
		];
    }
}
