<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterDealerResourceList extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
			'text' 			=> $this->title,
		    'business'		=> $this->business,
		    'contact'		=> $this->contact,
		    'email' 		=> $this->email,
		    'address1'		=> $this->address1,
		    'address2'		=> $this->address2,
		    'address3'		=> $this->address3,
		    'towncity'		=> $this->towncity,
		    'county'		=> $this->county,
		    'postcode'		=> $this->postcode,
		    'latitude'		=> $this->latitude,
		    'longitude'		=> $this->longitude,
		    'tel'			=> $this->tel,
		    'mobile'		=> $this->mobile,
		    'website'		=> $this->website,
		    'logo'			=> $this->setlogo($this->logo),
		    'background'	=> $this->background,
		    'logo'			=> $this->logo,
		    'logintitle'	=> $this->logintitle,
		   	'user'			=> $this->user 
		   
		];
    }
}
