<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterCompanyResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
		    'subs'			=> CasterNtripSubscriptionResourceEdit::collection($this->subs),
			'value' 		=> $this->id,
			'text' 			=> $this->title,
		   	'business'		=> $this->business,
		    'dealer'		=> $this->dealer,
		    'contact'		=> $this->contact,
		    'email' 		=> $this->email,
		    'address1'		=> $this->address1,
		    'address2'		=> $this->address2,
		    'address3'		=> $this->address3,
		    'towncity'		=> $this->towncity,
		    'county'		=> $this->county,
		    'postcode'		=> $this->postcode,
		    'tel'			=> $this->tel,
		   	'mobile'		=> $this->mobile,
		    'website'		=> $this->website,
		    'latitude'		=> $this->latitude,
		    'longitude'		=> $this->longitude,
		    'account'		=> $this->account,
		    'logo'			=> $this->logo,
		    'logintitle'	=> $this->logintitle,
		    
		    'users'			=> UserResource::collection($this->users),
		    'machines'		=> CompanyMachineResource::collection($this->machines),
		   	'usercount'		=> $this->userscount(21)+$this->userscount(20),
		    'admincount'	=> $this->userscount(20),
		    'machinecount'	=> $this->machinecount(),
		    'rovercount'	=> $this->rovercount()
		];
    }
}
