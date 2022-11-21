<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterUserResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
			'firstname' 	=> $this->forename,
		    'surname' 		=> $this->surname,
		    'company'		=> $this->company,
		    //'companydetail'	=> $this->companydetail,
		   	'dealer'		=> $this->dealer,
		    //'dealerdetail'	=> $this->dealerdetail,
		    //'companytitle'	=> $this->companydetail['title'],
//		    'pcompany'		=> $this->pcompany,
//		    'pcompanytitle'	=> 'TEST',//$this->pcompanydetail['title'],
		    'role'			=> $this->role,
		    'name'			=> $this->forename.' '.$this->surname,
		    'username'		=> $this->username,
		    //'text'			=> $this->companydetail['title'].' - '.$this->username, 
		    'password'		=> $this->password,
		    'email'			=> $this->email,
		    'email2'		=> $this->email2,
		    'phone'			=> $this->phone,
		    'mobile'		=> $this->mobile,
		    'subscription'	=> $this->subscription,
		    //'subscriptiondetail'	=> $this->subscriptions,
		    //'auto_renew'	=> 'no',//$this->setrenew($this->auto_renew),
		    'notes'			=> $this->notes
		];
    }
}
