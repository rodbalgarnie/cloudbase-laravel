<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource as RoleResource;

class RolesController extends Controller
{

	
   public function index(Request $request)
    {
	   $role = $request->role;
	  
       if($role == 1){$roles = Role::get();} else $roles = Role::where('id','!=',1)->get();
			
	   return array('roles'=>RoleResource::collection($roles));//
    }

  
    public function store(Request $request)
    {
		
        $role = $request->isMethod('put') ? Role::findorfail($request->value) : new Role;
		$role->id = $request->value;
		$role->title = $request->text;
		
		if($role->save())
		{
			return new RoleResource($role);
		}    
	}

   
    public function show($id)
    {
        $role = Role::findorfail($id);
		return new RoleResource($role);
    }


    public function destroy($id)
    {
        $role = Role::findorfail($id);
		if($role->delete()){
			return new RoleResource($role);
		}
    }
}
