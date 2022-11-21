<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterEmailLogResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		    'subject'		=> $this->title,
		   	'text' 			=> $this->text,
		    'sent_email'	=> $this->sent_email,
		    'type'			=> $this->email_type,
		   	'reseller'		=> $this->reseller,
		    'resellertitle'	=> $this->resellers['title'], 
		    'dealer'		=> $this->dealer,
		    'dealertitle'	=> $this->dealers['title'], 
		   	'company'		=> $this->company,
		   	'companytitle'	=> $this->companies['title'],
		    'date'			=> date('Y-m-d H:i:s',strtotime($this->created_at)) 
		];
    }
}
