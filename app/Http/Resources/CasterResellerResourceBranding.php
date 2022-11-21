<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterResellerResourceBranding extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
			'text' 			=> $this->title, 
		    'business'		=> $this->business, 
		    'logintitle'	=> $this->logintitle,
		    'logo'			=> '/images/branding/logos/'.$this->logo,
			'background'	=> '/images/branding/homebgs/'.$this->background
		];
    }
}
