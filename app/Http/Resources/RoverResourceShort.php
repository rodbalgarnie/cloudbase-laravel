<?php
// Version 290922/1700
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoverResourceShort extends JsonResource
{
   
   public function toArray($request)
    {
       return [
			'value' 		=> $this->id,
			'text' 			=> $this->title,
		    'dealer'		=> $this->dealer,
		    'company'		=> $this->company,
		    'machine'		=> $this->machines,
		    'simm'			=> $this->simm,
		    'simm_ccid'				=> $this->simms2['iccid'], //['number'],
		    'simm_dataused'			=> $this->simms2['dataused'],
		   	'simm_dataremaining'	=> $this->simms2['dataremaining'],
		   // 'subscriptiondetail' => CasterNtripSubscriptionResourceRover::collection($this->subscriptions),
		    'rtk_status'	=> $this->rtk_status,
		    'color'			=> $this->getRTKStatus['colour'],
		   	'statustext'	=> $this->getRTKStatus['message'],
		    'lastsession'	=> $this->lastsession($this->id),
		   	'lastgga'		=> $this->lastgga($this->last_mesg),
		    'lastlog'		=> $this->lastlog($this->last_log),
		    'lastlogid'		=> $this->last_log,
		    'lastconnect'	=> $this->last_connect,
		   	'lastconnect2'	=> $this->last_connect,
		    'fixtime'		=> $this->fix_time,
		   	'username'		=> $this->username,
		    'password'		=> $this->password,
		    'created'		=> date('d-m-y H:i',strtotime($this->updated_at))
		
		];
    }
}
