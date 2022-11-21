<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\CasterDataPollLog;
use App\Http\Controllers\Controller; 
use App\Http\Resources\CasterDataPollResource as CasterDataPollResource;


class CasterDataPollController extends Controller
{
	
   public function index(Request $request)
    {
	   $logs = CasterDataPollLog::get();
	   return array('count'=>count($logs),'CasterPollData'=>CasterDataPollResource::collection($logs));//
    }
	
	public function store(Request $request)
    {
		$log = $request->isMethod('put') ? CasterPollData::findorfail($request->value) : new CasterPollData;
		$log->baselogs = $request->baselogs;
		$log->roverlogs = $request->roverlogs;
		$log->ggas = $request->ggas;
		$log->rtcm3 = $request->rtcm3;
		$log->save();
		return new CasterDataPollResource($log); 
	}

}