<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasterDataPollResource extends JsonResource 
{
   
   public function toArray($request)
    {
       return [
			'id' 		=> $this->id,
			'baselogs' 	=> $this->baselogs,
		    'roverlogs'	=> $this->roverlogs,
		    'ggas'		=> $this->ggas,
			'rovers'	=> $this->rovers,
		   	'errors'	=> $this->errors 
		];
    }
}
