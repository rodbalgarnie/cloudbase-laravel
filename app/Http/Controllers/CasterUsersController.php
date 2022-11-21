<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CasterUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\CasterUserResource as CasterUserResource;

class CasterUsersController extends Controller
{

	
   public function index(Request $request)
    {
	   $id = $request->user;
	   $stext = $request->stext;
	   $admin = $request->admin;
	   $dealer = $request->dealer;
	   $company = $request->company;
	   $role = $request->role;
	   
	 
	   
	   $users = CasterUser:://with('companydetail')
	   	when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
		->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})	
		->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})		
		->when($role > 0, function ($q) use($role){
					return $q->where('role',$role);
			})   
		->when($stext != '', function ($q) use($stext) {
				return $q->whereHas('companydetail', function($q) use($stext){
    				$q->where('title','LIKE',$stext.'%')->orWhere('surname','LIKE',$stext.'%')->orWhere('username','LIKE',$stext.'%');
					});
			})
		->where('archive',0)	
		->orderBy('surname','ASC')   
		->get();
	   return array('CasterUsers'=>CasterUserResource::collection($users));//
    }

  
    public function store(Request $request)
    {
		$user = $request->isMethod('put') ? CasterUser::findorfail($request->value) : new CasterUser;
		$user->id = $request->value;
		$user->forename = $request->firstname;
		$user->surname = $request->surname;
		$user->username = $request->username;
		//$user->password = $request->password;
		$user->company = $request->company;
		$user->dealer = $request->dealer;
		$user->email = $request->email;
		$user->email2 = $request->email2;
		$user->phone = $request->phone;
		$user->mobile = $request->mobile;
		$user->subscription = $request->subscription;
		$user->notes = $request->notes;
		$user->save();
		return ;
	}
	
	public function storecontact(Request $request)
    {
		$user = $request->isMethod('put') ? CasterUser::findorfail($request->value) : new CasterUser;
		$user->id = $request->value;
		$user->email = $request->email;
		$user->email2 = $request->email2;
		$user->phone = $request->phone;
		$user->mobile = $request->mobile;
		$user->notes = $request->notes;
		$user->save();
		return ;
	}

	public function storedetail(Request $request)
    {
		$user = $request->isMethod('put') ? CasterUser::findorfail($request->value) : new CasterUser;
		$user->id = $request->value;
		$user->forename = $request->firstname;
		$user->surname = $request->surname;
		$user->company = $request->company;
		$user->pcompany = $request->pcompany;
		$user->save();
		return ;
	}
	
	 public function archive(Request $request)
    {
	    $user = CasterUser::where('id',$request->id)->first();
		$user->archive = 1;
		$user->save();
		return;
	 }
   
    public function show($id)
    {
        $user = CasterUser::findorfail($id);
		return new CasterUserResource($user);
    }


    public function destroy($id)
    {
        $user = CasterUser::findorfail($id);
		if($user->delete()){
			return new CasterUserResource($user);
		}
    }
}
