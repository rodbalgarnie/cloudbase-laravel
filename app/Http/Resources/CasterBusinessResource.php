<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterBusinessResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id, 
			'text' 			=> $this->title,
		   
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
		    'users'			=> UserResource::collection($this->users),
		    'logintitle'	=> $this->logintitle,
		    'logo'			=> $this->logo,
		    'background'	=> $this->background
		    //'logo'			=> $this->setlogo($this->logo),
		    //'depots'		=> $this->depots,
		    //'admins'		=> $this->userscount(10),
		    //'users'			=> $this->userscount(11)+$this->userscount(10),
		    //'companies'		=> $this->companycount(),
		    //'subs'			=> $this->subcount(),
		    //'rovers'		=> $this->rovercount()
		   // 'subscriptions' => CasterNtripSubscriptionResource::collection($this->subscriptions)
		   
		];
    }
}
