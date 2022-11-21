<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\CasterStat;
use App\Http\Controllers\Controller; 
use App\Http\Resources\CasterStatResource as CasterStatResource;

class CasterStatsController extends Controller
{
	
   public function index(Request $request)
    {
	   $group = $request->group;
	   
	   $types = CasterStat::
	   when($group != '', function ($q) use($group) {
					return $q->where('group',$group);
			})	
		->get();
	   return array('CasterStats'=>CasterStatResource::collection($types));//
    }
	
	
	
    public function destroy($id)
    {
        $stat = CasterEvent::findorfail($id);
		$stat->delete();
		return;
    }
}
