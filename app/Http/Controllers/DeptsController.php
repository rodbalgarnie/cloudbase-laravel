<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Dept;
use App\UserDept;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeptResource as DeptResource;
use App\Http\Resources\UserDeptResource as UserDeptResource;
//use JWTAuth;


class DeptsController extends Controller
{
//	public function __construct()
//	{
//	
//	$this->user = JWTAuth::parseToken()->authenticate();
//  	
//	}
	
   public function index()
    {
       $depts = Dept::paginate(15);
	   return array('depts'=>DeptResource::collection($depts));//
    }

	
//	public function postindex()
//    {
//       $depts = Dept::paginate(15);
//	   return array('depts'=>DeptResource::collection($depts));//
//    }

  
    public function store(Request $request)
    {
        $dept = $request->isMethod('put') ? Dept::findorfail($request->value) : new Dept;
		$dept->id = $request->value;
		$dept->title = $request->text;
		
	
		if($dept->save())
		{
			return new DeptResource($dept);
		}    
	}

   
    public function show($id)
    {
        $dept = Dept::findorfail($id);
		return new DeptResource($dept);
    }


    public function destroy($id)
    {
        $dept = Dept::findorfail($id);
		if($dept->delete()){
			return new DeptResource($dept);
		}
    }
	
	
	// Procure Cost Code Depts Functions
	
	 public function indexpr(Request $request)
    {
       $depts = UserDept::where('user',$request->user)->get();
	   return array('depts'=>UserDeptResource::collection($depts));//
    }

	
	
}
