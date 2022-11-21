<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CompanyMachine;
use App\MachineMaker;
use App\MachineModel;
//use App\Photo;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyMachineResource as CompanyMachineResource;

class CompanyMachinesController extends Controller
{

	
   public function index(Request $request) 
    {
	   $id = $request->id;
	   $company = $request->company;
	   $filter = $request->filter;
	   $implement = $request->implement;
	   
	  
	   $machines = CompanyMachine::
//	   		when($implement != 99, function ($q) use($implement) {
//					return $q->where('implement',$implement);
//			})		
			when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
			->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})	
			->when($filter == 1, function ($q)  {
					return $q->where('rover',0);
			})		
			->orderBy('rover','ASC')
			->get();
		   
	  
	   return array('machines'=>CompanyMachineResource::collection($machines));//
    }
	
	public function CompanyMachineslist(Request $request) 
    {
	   
	   $id = $request->id;
		
	   $machines = CompanyMachine::
			when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
			->OrderBy('text','asc')	
			->get();
	   return array('machines'=>CompanyMachineResource::collection($machines));//
    }


  
    public function store(Request $request){
		
		
    
		if(isset($request->value)){$id = $request->value;}
		if(isset($request->id)){$id = $request->id;}
		
		$machine = $request->isMethod('put') ? CompanyMachine::findorfail($id) : new CompanyMachine;
		//$machine->id = $id;
		$machine->company = $request->company;
		$machine->type = $request->type;
		$machine->make = $request->make;
		$machine->model = $request->model;
		$machine->regnum = $request->regnum;
		$machine->receiver_serial_num = $request->receiver_serial_num;
		$machine->modem_serial_num = $request->modem_serial_num;
		//$machine->implement = $request->implement;
		
		$make = MachineMaker::where('id',$request->make)->first();
		$model = MachineModel::where('id',$request->model)->first();
		
		$machine->text = $make->title.' '.$model->title.' '.$request->regnum;
		
		$machine->save();
		
		return $machine->id;
	}

   
    public function show($id)
    {
        $machine = CompanyMachine::findorfail($id);
		return new CompanyMachineResource($machine);
    }
	
	public function destroy($id)
    {
        $machine = CompanyMachine::where('id',$id)->delete();
		return ;
    }


}
