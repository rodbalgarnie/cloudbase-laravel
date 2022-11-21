<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Photo;
use Storage;
use File;
use Intervention\Image\ImageManagerStatic as Image;
use App\Http\Controllers\Controller;
use App\Http\Resources\PhotoResource as PhotoResource;

class PhotosController extends Controller
{

	
   public function index(Request $request) 
    {
	   $parent = $request->parent;
	   $type = $request->type;
	   
	   $photos = Photo::
	   		where('parent',$parent)
			->where('type',$type)	
			->get();
	   
	   return array('photos'=>PhotoResource::collection($photos));//
	
    }
	
  
    public function store(Request $request)
    {
		
	    $photo = $request->isMethod('put') ? Photo::findorfail($request->value) : new Photo;
		$photo->id = $request->value;
		$photo->parent = $request->parent;
		$photo->type = $request->type;
		$photo->filename = $request->filename;
		$photo->save();
		return new PhotoResource($photo);
	}
	
	public function photodelete(Request $request){
		
		$filename = $request->filename;
		$dir = '/var/www/ip-rtk-aws.com/httpdocs/public/images/photos/'; 
		$thumbs = '/var/www/ip-rtk-aws.com/httpdocs/public/images/photos/mcith/'; 
		
		$del = File::delete($dir.$filename);
		$delp = Photo::where('filename',$filename)->delete();
		
		$filename = 'mcith_'.$filename;
		$del = File::delete($thumbs.$filename);
		
		return;
	}

   
    public function show($id)
    {
        $photo = Photo::findorfail($id);
		return new PhotoResource($photo);
    }
	
	public function photoupload(Request $request){
			
		$error = "ok";
		$image = $request->file('files');
		$type = $request->type;
		$filename = $image->getClientOriginalName();
		$dir = '/var/www/ip-rtk-aws.com/httpdocs/public/images/'.$type.'/'; 
		
		if(File::exists($dir.$filename)) {
			 $filename = time().'_'.$filename;
		} 
		$image->move($dir,$filename);
		
		return array('error'=>$error,'filename'=>$filename); // $photo->image_id;
		
	}
	
	public function photouploadmulti(Request $request){
		
		$filenames =   array();
		
		$type = $request->type;
		$parent = $request->parent;
		
		$file_names = $_FILES['files']['name'];
		
		foreach ($request->file('files') as $image) {
                $filename = $image->getClientOriginalName();
        
		$dir = '/var/www/ip-rtk-aws.com/httpdocs/public/images/branding/homebgs/';  
		$thumbs = '/var/www/ip-rtk-aws.com/httpdocs/public/images/photos/mcith/'; 
		
		if(File::exists($dir.$filename)) {
			 $filename = time().'_'.$filename;
		} 
		$image->move($dir,$filename);
		
		$image_resize = Image::make($dir.$filename);              
   		$image_resize->widen(1024);
    	$image_resize->save($dir.$filename);
		
		$filenameth = 'mcith_'.$filename;
		$image_resize->widen(500);
    	$image_resize->save($thumbs.$filenameth);
		
		$photo = new Photo;
		$photo->parent = $parent;
		$photo->type = $type;
		$photo->filename = $filename;
		$photo->save();
			
		$filenames[] = $filename;	
		
		}
		return array('filenames'=>$filenames);
		
	}
	
	public function photorotate(Request $request){
		
		
		$filename =$request->filename;
		
		$dir = '/var/www/ip-rtk-aws.com/httpdocs/public/images/photos/'; 
		$thumbs = '/var/www/ip-rtk-aws.com/httpdocs/public/images/photos/mcith/'; 
		
		
		$image = Image::make($dir.$filename);  
		$image->rotate(-90);
		$image->save($dir.$filename);
		
		$filename = 'mcith_'.$filename;
		
		$image = Image::make($thumbs.$filename);  
		$image->rotate(-90);
		$image->save($thumbs.$filename);
		
		return $request;
	}
	
	public function photosetmain(Request $request){
		
		$filename = $request->filename;
		$type = $request->type;
		$parent = $request->parent;
		
		$photos = Photo::
			where('parent',$parent)	
			->where('type',$type)
			->get()
			->toArray();
		
		foreach($photos as $photo){
			$clear = Photo::where('id',$photo['id'])->first();
			$clear->main = 0;
			$clear->save();
		}
		
		$photo = Photo::
			where('filename',$filename)
			->where('parent',$parent)		
			->where('type',$type)
			->first();
		
		$photo->main = 1;
		$photo->save();
		
		return;
	}
}
