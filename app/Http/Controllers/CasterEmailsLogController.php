<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\CasterEmailsLog;
use App\Http\Controllers\Controller; 
use App\Http\Resources\CasterEmailLogResource as CasterEmailLogResource;

class CasterEmailsLogController extends Controller 
{
	
   public function index(Request $request)
    {
	   $reseller = $request->reseller;
	   $dealer = $request->dealer;
	   $company = $request->company;
	   
	   $emails = CasterEmailsLog::
	   		when($reseller > 0, function ($q) use($reseller) {
					return $q->where('reseller',$reseller);
			})
	   		->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})
			->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})
			->get();
	   
	   return array('CasterEmails'=>CasterEmailLogResource::collection($emails));//
    }
	
	public function store(Request $request)
    {
		$email = $request->isMethod('put') ? CasterEmailsLog::findorfail($request->value) : new CasterEmailsLog;
		$email->id = $request->value;
		$email->text = $request->text;
		$email->save();
		return new CasterEmailLogResource($email);
	}
	
	
    public function destroy($id)
    {
        $email = CasterEmailsLog::findorfail($id);
		$email->delete();
		return;
    }
}
