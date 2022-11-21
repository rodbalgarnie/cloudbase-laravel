<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		    'fname' 		=> $this->fname,
		    'lname' 		=> $this->lname,
		    'reseller'		=> $this->business,
		    'resellertitle'	=> $this->resellers['title'],
		    'dealer'		=> $this->dealer,
		    'dealertitle'	=> $this->dealers['title'],
		    'company'		=> $this->company,
		    'companytitle'	=> $this->companies['title'],
		    'role'			=> $this->role,
		    'roletitle'		=> $this->roles['title'],
		    'title'			=> $this->fname.' '.$this->lname,
		    'password'		=> '******',//$this->password,
		    'changepassword' => '',
		    'email'			=> $this->email,
		    'phone'			=> $this->phone,
		    'mobile'		=> $this->mobile,
		    'readonly'		=> $this->readonly(),
		    'notes'			=> $this->notes,
		    'created'		=> date('d-m-Y H:i',strtotime($this->updated_at))
		];
    }
}
