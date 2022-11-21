<?php
// Version 230922/1600
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterSimmResource extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
		   	'text'			=> $this->iccid,
			'dealer' 		=> $this->dealer,
		    'dealername'	=> $this->dealers['title'],
		    'company'		=> $this->company,
		   	'companyname'	=> $this->companys['title'],
		    'rover'			=> $this->rover,
		    'rovername'		=> $this->rovers['title'],
		   	'stock'			=> $this->stock,
		    'package_id'	=> $this->package_id,
		    'package'		=> $this->simmpackages['text_short'],
		    'connection_id' => $this->connectionid,
		    'supplier'		=> $this->supplier,
		    'service'		=> $this->service,
		    'online'		=> $this->online,
		    'status'		=> $this->getstatus['text'],
		    'statusid'		=> $this->getstatus['id'],
		    'dataused'		=> $this->datausedmonth,
		   	'dataremaining'	=> $this->dataremaining,
		    'number'		=> $this->iccid,
		   	'apn'			=> $this->apn,
		    'action'		=> 0
		];
    }
}
