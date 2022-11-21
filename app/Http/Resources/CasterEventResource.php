<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterEventResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		    'text'			=> $this->text,
		   	'dealer' 		=> $this->dealer,
		    'dealername'	=> $this->dealers['title'],
		    'company'		=> $this->company,
		   	'companyname'	=> $this->companys['title'],
		    'rover'			=> $this->rover,
		    'rovername'		=> $this->rovers['title'],
		   	'user'			=> $this->user,
		    'username'		=> $this->users['fname'].' '.$this->users['lname'],
		    'type'			=> $this->type,
		    'eventgroup'	=> $this->eventgroup,
		    'color'			=> $this->types['color'],
		    'typename'		=> $this->types['text'],
		    'email1'		=> $this->email1,
		    'email2'		=> $this->email2,
		    'datetime'		=> date('d-m-Y H:i:s',strtotime($this->created_at))
		];
    }
}
