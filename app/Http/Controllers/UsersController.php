<?php
// Version 260722/1100
namespace App\Http\Controllers ;

use Illuminate\Http\Request; 
use App\Http\Requests;
use App\User;
use App\CasterCompany;
use Storage;
use File;
use Intervention\Image\ImageManagerStatic as Image;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource as UserResource;
use Auth;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
	
    public function index(Request $request)
    {
		
		if($request->type == ''){$type = 0;} else $type = $request->type;
		$stext = $request->stext;
		$id = $request->id;
		
		if($request->all == 1){
			
			$users= User::
			where('id',$request->id)
			->where('archive',0)	
			->orderBy('fname','asc')
			->get();
			
		} else 
		
		if($request->role ==1){
			
			$users= User::
			where('role',1)	
			->where('archive',0)	
			->orderBy('fname','asc')
			->get();
			
		} else {
			
		
		$users= User::
			when($id != 0, function ($q) use ($id) {
    			return $q->where('id',$id);
			})	
			->when(request('username') != "", function ($q) {
    			return $q->where('email',request('username'));
			})
			->when(request('email') != "", function ($q) {
    			return $q->where('email',request('email'));
			})	
			->when(request('user') != 0, function ($q) {
    			return $q->where('user',request('user'));
			})
			->when(request('role') != 0, function ($q) {
    			return $q->where('role',request('role'));
			})	
			->when(request('reseller') != 0, function ($q) {
    			return $q->where('business',request('reseller'));
			})	
			->when(request('dealer') != 0, function ($q) {
    			return $q->where('dealer',request('dealer'));
			})
			->when(request('company') != 0, function ($q) {
    			return $q->where('company',request('company'));
			})	
			->when(request('role') != 0, function ($q) {
    			return $q->where('role',request('role'));
			})	
			->when(request('stext') != '', function ($q) use ($stext){
    			return $q->where('email','LIKE','%'.$stext.'%');
			})
			//->where('role','!=',1)	
			->where('archive',0)	
			->orderBy('role','asc')
			->get();
			
		}
	
			$total = count($users);
	
	   		return array('total'=>$total,'users'=>UserResource::collection($users));//
    }
  
    public function store(Request $request)
    {
		$setpassword = 0;
			
		if($request->dealer == 0 && $request->role > 5){
			$getdealer = CasterCompany::where('id',$request->company)->first();
			$dealer = $getdealer->dealer;
		} else $dealer = $request->dealer;
		
		
		if($request->changepassword != ''){
			$password = Hash::make($request->changepassword);
			$setpassword = 1;
		}
		
		if(!$request->value){
			$password = Hash::make($request->password);
			$setpassword = 1;
		}
		
		$readonly = $request->readonly;
		if($readonly== null){$readonly = 0;}
		
		if($request->archive == null){$archive = 0;} else $archive= $request->archive;
		
		if($request->value == 0){$user = new User;} else $user = User::findorfail($request->value);
		//$user = $request->isMethod('put') ? User::findorfail($request->value) : new User;
		$user->id = $request->value;
		$user->fname = $request->fname;
		$user->lname = $request->lname;
		$user->email = $request->email;
		$user->role = $request->role;
		$user->business = $request->reseller;
		$user->dealer = $dealer;
		$user->company = $request->company;
		$user->mobile = $request->mobile;
		$user->phone = $request->phone;
		$user->readonly = $readonly;
		if($setpassword == 1){
			$user->password = $password;
		}
		$user->save();
		
		return new UserResource($user);
		    
	}
	
	 public function checkuserexists(Request $request)
    {
		
		$email = $request->email;
			
		$user = User::
			where('email',$email)	
			->first();
		
		if($user == null){return 'false';} else return 'true';
    }
	
	 public function storeprofile(Request $request)
    {
		if($request->changepassword != ''){
			$password = $request->changepassword;
			$setpassword = 1;
		} else $setpassword = 0;
		
		
		$user = User::findorfail($request->value);
		$user->fname = $request->fname;
		$user->lname = $request->lname;
		$user->email = $request->email;
		$user->mobile = $request->mobile;
		$user->phone = $request->phone;
		if($setpassword == 1){
			$user->password = Hash::make($password);
		}
		$user->save();
		
		return new UserResource($user);
		    
	}
	
	public function getauthuser(){
		
		if (Auth::check()) {
    // The user is logged in...
			return "YYY";
		} else return "NNNN";
		
//		$user = Auth::user();
//		return "xxx".$user;
	}
	
	 public function archive(Request $request)
    {
	    $user = User::where('id',$request->id)->first();
		$user->archive = 1;
		$user->save();
		return;
	 }

   
    public function show(Request $request)
    {
        $user = User::findorfail($request->id);
		return new UserResource($user);
    }


    public function destroy($id)
    {
        $user = User::findorfail($id);
		$user->delete();
		return new UserResource($user);
		
    }
	
	
	
}