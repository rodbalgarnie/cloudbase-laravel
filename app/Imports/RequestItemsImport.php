<?php

namespace App\Imports;

use App\PurchrequestItem;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;

class RequestItemsImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row)
    {
        return new PurchrequestItem([
          	'product_title'     		=> $row[0],
          	'product_request_notes'   	=> $row[1], 
          	'product_request_quant' 	=> $row[2],
			'product_request_cost'		=> $row[3],
			'product_link'				=> $row[4]
			
			
        ]);
    }
}