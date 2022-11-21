<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\CasterEmail;
use App\Http\Controllers\Controller; 
use App\Http\Resources\CasterEmailResource as CasterEmailResource;

class CasterEmailsController extends Controller
{
	
   public function index(Request $request)
    {
	   $types = CasterEmail::get();
	   return array('CasterEmails'=>CasterEmailResource::collection($types));//
    }
	
	public function store(Request $request)
    {
		$email = $request->isMethod('put') ? CasterEmail::findorfail($request->value) : new CasterEmail;
		$email->id = $request->value;
		$email->text = $request->text;
		$email->save();
		return new CasterEmailResource($email);
	}
	
	
    public function destroy($id)
    {
        $email = CasterEmail::findorfail($id);
		$email->delete();
		return;
    }
}
