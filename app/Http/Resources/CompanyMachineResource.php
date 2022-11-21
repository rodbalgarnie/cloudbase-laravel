<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyMachineResource extends JsonResource 
{
   
   public function toArray($request) 
    {
       return [
			'value' 			=> $this->id,
		    'id'	 			=> $this->id,
		    'text'				=> $this->makes['title'].' '.$this->models['title'].' '.$this->regnum,
		   	'make'				=> $this->make,
		    'make_title'		=> $this->makes['title'],
		    'model'				=> $this->model,
		   	'model_title'		=> $this->models['title'],
		   	'type'				=> $this->type,
		   	'type_title'		=> $this->types['title'],
			'note' 				=> $this->note,
		    'regnum' 			=> $this->regnum,
		    //'rover'				=> $this->rovers['title'],
		    'rover'				=> $this->rover,
		    //'company'			=> $this->companies['title'],
		    'dealer'			=> $this->dealer, 
		    'company'			=> $this->company,
		    'modem_serial_num' => $this->modem_serial_num,
		    'receiver_serial_num' => $this->receiver_serial_num,
		    'sub'				=> CasterNtripSubscriptionResourceList::collection($this->subs)
		   // 'image'				=> $this->mainphoto(),
		   // 'notes' 			=> NoteResource::collection($this->notes),
		   // 'photos' 			=> PhotoResource::collection($this->photos),
		   // 'notecount'			=> count($this->notes)
		   
		];
    }
}
