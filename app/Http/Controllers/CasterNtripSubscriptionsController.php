<?php
// Version 091122
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CasterNtripSubscription; 
use App\CasterSubscription;
use App\CasterNtripSubscriptionStock;
use App\CasterSubStatus;
use App\CasterSimm;
use App\CasterBusiness;
use App\CasterDealer;
use App\CasterCompany;
use App\Rover;
use App\System;
use App\CompanyMachine;
use App\Http\Controllers\Controller;
use App\Http\Resources\CasterNtripSubscriptionResource as CasterNtripSubscriptionResource;
use App\Http\Resources\CasterNtripSubscriptionResourceVShort as CasterNtripSubscriptionResourceVShort;
use App\Http\Resources\CasterNtripSubscriptionResourceShort as CasterNtripSubscriptionResourceShort;
use App\Http\Resources\CasterNtripSubscriptionStatusResource as CasterNtripSubscriptionStatusResource;
use App\Http\Resources\CasterNtripSubscriptionResourceEdit as CasterNtripSubscriptionResourceEdit;
use DB;
use App\User;
use App\CasterEmail;
use App\CasterEmailsLog;
use App\Mail\SendEmail;
use Mail;

class CasterNtripSubscriptionsController extends Controller
{
	
	// external non-sanctum api call checksubsexpiry via CRON job on Cloudbase CRM Server.
	// called every hour to check for expiring and renewing subscriptions and update accordingly.
	public function checksubsexpiry(){
		
	$now = strtotime(date('Y-m-d H:i:s'));	// time in seconds
	$checkdate = date('Y-m-d H:i:s',strtotime ( '+31 days',$now));
		
	$subs = CasterNtripSubscription::
		where('status',3)	
		->where('enddate','<=',$checkdate)	
		->get();	//	Get all active subs expring in less than 31 days;
	
		
	foreach($subs as $sub){
		
		$emailid = 0;
		$numdays = 0;
		$expire = (strtotime($sub->enddate) - $now)/(24 * 60 * 60); 	// get number of days to expiry
		
			if($expire < 0){		//	Expired
				$emailid = 4;
				$numdays = 0;
				$sub->status = 4;
				$sub->active = 0;
				$sub->renew_once = 0; // Added 26/10/22 
			} else

			if($expire > 7 && $expire < 30 && $sub->thirtyday == 0){	//	30 Day warning
				$emailid = 2;
				$numdays = 30;
				$sub->thirtyday = 1;
			} else

			if($expire > 1 && $expire < 7 && $sub->sevenday == 0){		//	7 Day warning
				$emailid = 2;
				$numdays = 7;
				$sub->sevenday = 1;
			} else

			if($expire > 0 && $expire < 1 && $sub->oneday == 0){		//	1 Day warning
				$emailid = 3;
				$numdays = 1;
				$sub->oneday = 1;
			} 

			if($emailid !== 0 && $sub->renewsent == 0){	// Only send warning if not been renewed

				$company = CasterCompany::where('id',$sub->company)->first();
				$useremail = $company->email;
				$user['role'] = 10;
				$user['id'] = 1;
				
				$response = $this->createemail($user,$useremail,$emailid,$sub->rover,$numdays);	
				

			}
		
			$sub->save(); 
	}	
		
		
		
		// Deal with RENEW ONCE subs = LOOKS FOR ANY SUBSCRIPTION EXPIRING IN LESS THAN 12 hours
		
		$checkdate = date('Y-m-d H:i:s',strtotime ( '+12 hours',$now));	
	
		$subs = CasterNtripSubscription::with('users')
		->where('renew_once',1)
		->where('enddate','<=',$checkdate)
		->where('status',3)	
		->get();	//	Get all subs waiting for renewal in the next 12 hours
	
	
		foreach($subs as $sub){
			
			$renewalstocksub = CasterNtripSubscriptionStock::	// Get waiting renewal stock subscription
				//with('subscription')
				where('sub_id',$sub->id)
				->where('status',2)
				->first();
			
			if($renewalstocksub !== null){
			
				$currentstocksub = CasterNtripSubscriptionStock::	// Expire current stock sub
					where('sub_id',$sub->id)
					->where('status',3)	
					->first();
				
				if($currentstocksub !== null){
					$currentstocksub->status = 4;
					$currentstocksub->save();
				}

				$renewalstocksub->status = 3;	// Set new stock sub to live
				$renewalstocksub->save();


				$livesub = CasterNtripSubscription::				// Get live stock subscription and update with renewal
					where('id',$sub->id)
					->first();

				$livesub->type = $renewalstocksub->type;
				$livesub->stocksub = $renewalstocksub->id;
				$livesub->startdate = $renewalstocksub->startdate;
				$livesub->enddate = $renewalstocksub->enddate;
				$livesub->thirtyday = 0;
				$livesub->sevenday = 0;
				$livesub->oneday = 0;
				$livesub->status = 3;
				$livesub->renew_once = 0;
				$livesub->renewsent = 0;
				$livesub->save();

				$rover = Rover::where('id',$renewalstocksub->rover)->first();

				// Update Caster with new renewal

				$result =  DB::connection('mysql2')->update("update subscriptions set Expiry_date = '$renewalstocksub->enddate' where Username = ?", array($rover->username));
				
			}
			
		}
		
		
	//Update last run datetime
	$system = System::where('id',1)->first();
	$system->lastchecksubexpiry = date('Y-m-d H:i:s');
	$system->save();	
	return;
		
	}
	
	
	public function sendmail(Request $request){
		
	
	$emailid = $request->emailid;
	$useremail = $request->useremail; 
	$user = $request->user;	
	
	
	if($emailid == 10){							// User login details email
			$data = $request->data;	
			$response = $this->createadminemail($user,$useremail,$emailid,$data);
	} else {
				$subdays = $request->subdays;
				$rover = $request->rover;
				$response = $this->createemail($user,$useremail,$emailid,$rover,$subdays);	
			}
		
	return $response;
		
	}
	
	
	
	public function createadminemail($user,$useremail,$emailid,$data){
		
				if($data['dealer'] > 0){
					$dealer = CasterDealer::where('id',$data['dealer'])->first();
				} else $dealer = CasterDealer::where('id',2)->first();
		
				$logo = $dealer->logo;
				$portal = $dealer->logintitle;		
				$contact = $dealer->contact;
				$email = $dealer->email;
				$tel = $dealer->tel;
				$title = $dealer->title;
				$address1 = $dealer->address;
				$address2 = $dealer->address2;
				$address3 = $dealer->address3;
				$towncity = $dealer->towncity;
				$county = $dealer->county;
				$postcode = $dealer->postcode;
				$userdealer = $dealer->id;
		
		$logoimage = '<img src="https://ip-rtk-uk.com/images/branding/logos/'.$logo.'" style="height:40px" alt="Logo">';
		$portalurl = 'https://ip-rtk-uk.com/'.$portal.'/login';
		
		$userto = $useremail;
		$nameto = $data['fname'];
			
		$emailtemplate = CasterEmail::where('id',$emailid)->first();
		$subject = $emailtemplate->title;
		
		$email_text = nl2br($emailtemplate->text);
		$email_text = str_replace('[date]',date('d-m-Y'),$email_text);   
		$email_text = str_replace('[company contact name]',$nameto,$email_text);
		$email_text = str_replace('[dealer admin contact]',$contact,$email_text);   
		$email_text = str_replace('[dealer admin phone]',$tel,$email_text);
		$email_text = str_replace('[dealer admin email]',$email,$email_text);
		$email_text = str_replace('[dealer name]',$title,$email_text); 
		$email_text = str_replace('[dealer logo]',$logoimage,$email_text);
		$email_text = str_replace('[dealer portal url]',$portalurl,$email_text);
		
//		switch($data['role']){
//			case 5:		// Reseller Admin
//			$text = $data['resellertitle'];
//			$text2 = 'Reseller';	
//			break;
//			case 10:	// Dealer Admin
//			$text = $data['dealertitle'];
//			$text2 = 'Dealer';	
//			break;
//			case 20:		// Company Admin
//			$text = $data['companytitle'];
//			$text2 = 'Company';	
//			break;	
//		}
		
		$text = $dealer->title;
		$text2 = 'Dealer';	
		
		$name = $data['fname'].' '.$data['lname'];
		
			$email_text = str_replace('[user resellerdealercompany]',$text,$email_text);
			$email_text = str_replace('[resellerdealercompany]',$text2,$email_text);
			$email_text = str_replace('[user name]',$name,$email_text);
			$email_text = str_replace('[user role]',$data['roletitle'],$email_text);
			$email_text = str_replace('[user email]',$data['email'],$email_text);
			$email_text = str_replace('[user password]',$data['password'],$email_text);
		
		$details['logo'] = 'https://ip-rtk-uk.com/images/branding/logos/'.$logo;
		$details['subject'] = $subject;   

		$addresstext = $title;
		$addresstext = $addresstext.','.$address1;  
		if(isset($address2)){$addresstext = $addresstext.','.$address2;}
		if(isset($address3)){$addresstext = $addresstext.','.$address3;}
		$addresstext = $addresstext.','.$towncity;
		$addresstext = $addresstext.','.$county;
		$addresstext = $addresstext.','.$postcode;


		$details['footer'] = $addresstext; 
		$details['text1'] = $email_text;

		$this->storeemail($useremail,$subject,$email_text,$data['reseller'],$data['dealer'],$data['company'],$user['id'],$emailid);	
		
		$response = $this->sendemail($useremail,$details); // Mail::to($useremail)->send(new SendEmail($details));
		return $response;
		
	}
	
	
	public function createemail($user,$useremail,$emailid,$roverid,$subdays){ // Welcome and all sub emails
		
	// $user user that created the email
	// $useremail user email is sent to	
	if($emailid == 5){ // Renewal Email
		$sub = CasterNtripSubscriptionStock::where('rover',$roverid)->where('status',2)->first();
	} else $sub = CasterNtripSubscription::where('rover',$roverid)->first();
	//$roverid = $sub->rover;
	$emailtemplate = CasterEmail::where('id',$emailid)->first();
		
			$dealer = CasterDealer::where('id',$sub->dealer)->first();
			$logo = $dealer->logo;
			$portal = $dealer->logintitle;		
			$contact = $dealer->contact;
			$email = $dealer->email;
			$tel = $dealer->tel;
			$title = $dealer->title;
			$address1 = $dealer->address;
			$address2 = $dealer->address2;
			$address3 = $dealer->address3;
			$towncity = $dealer->towncity;
			$county = $dealer->county;
			$postcode = $dealer->postcode;
			$userdealer = $dealer->id;

		
		$logoimage = '<img src="https://ip-rtk-uk.com/images/branding/logos/'.$logo.'" style="height:40px" alt="Logo">';
		$portalurl = 'https://ip-rtk-uk.com/'.$portal.'/login';	
		
		$emailtemplate = CasterEmail::where('id',$emailid)->first();
		$subject = $emailtemplate->title;
		
		$nameto = 'not found';
		$companyto = 'not found';
		
		$usertocompany = CasterCompany::where('id',$sub->company)->first();
		$companyto = $usertocompany->title;
		$nameto = explode(' ',$usertocompany->contact)[0];
		
		if($useremail == ''){
			$useremail = $usertocompany->email;
		}

		
		$email_text = nl2br($emailtemplate->text);
		$email_text = str_replace('[date]',date('d-m-Y'),$email_text);   
		$email_text = str_replace('[company contact name]',$nameto,$email_text);
		$email_text = str_replace('[company name]',$companyto,$email_text);
		$email_text = str_replace('[dealer admin contact]',$contact,$email_text);   
		$email_text = str_replace('[dealer admin phone]',$tel,$email_text);
		$email_text = str_replace('[dealer admin email]',$email,$email_text);
		$email_text = str_replace('[dealer name]',$title,$email_text); 
		$email_text = str_replace('[dealer logo]',$logoimage,$email_text);
		$email_text = str_replace('[dealer portal url]',$portalurl,$email_text);
		
		// SIM Info
		$simm = CasterSimm::where('rover',$roverid)->first();
		if($simm != null){
			$iccid = $simm->iccid;
			$apn = $simm->apn;
		} else {
			$iccid = 'No supplied SIM';
			$apn = 'n/a';
			}
			
		
		// Rover de	
		$machine = CompanyMachine::where('rover',$roverid)->first();
		$machinetext = $machine->text;	
		// Subscription info
		
		
	
		$sub = Rover::with('subscriptions','subscriptions.subscription')->where('id',$roverid)->orderBy('id','DESC')->first();
		 
		$email_text = str_replace('[sub endpoint ip]',$sub->connection,$email_text);
		$email_text = str_replace('[sub port]',$sub->port,$email_text);  
//		$email_text = str_replace('[sub endpoint ip]','185.132.37.33',$email_text);
//		$email_text = str_replace('[sub port]','2101',$email_text);  
		$email_text = str_replace('[sub mountpoint]','RTK_RTCM3',$email_text);
		$email_text = str_replace('[sub username]',$sub->username,$email_text);
		$email_text = str_replace('[sub password]',$sub->password,$email_text);
		if($emailid == 5){
			$renewsub = CasterNtripSubscriptionStock::where('rover',$roverid)->where('status',2)->first();
			$renewsubtype = CasterSubscription::where('id',$renewsub->type)->first();
			$email_text = str_replace('[sub title]',$renewsubtype->title,$email_text);   
			$email_text = str_replace('[sub start]',date('d-m-Y H:i',strtotime($renewsub->startdate)),$email_text); 
			$email_text = str_replace('[sub expires]',date('d-m-Y H:i',strtotime($renewsub->enddate)),$email_text); 
		} else {
		$email_text = str_replace('[sub title]',$sub->subscriptions[0]->subscription[0]->title,$email_text);   	
		$email_text = str_replace('[sub start]',date('d-m-Y H:i',strtotime($sub->subscriptions[0]->startdate)),$email_text); 
		$email_text = str_replace('[sub expires]',date('d-m-Y H:i',strtotime($sub->subscriptions[0]->enddate)),$email_text);
		}
		$email_text = str_replace('[sub expire days]',$subdays,$email_text);
		$email_text = str_replace('[sub apn]',$apn,$email_text); 
		$email_text = str_replace('[iccid]',$iccid,$email_text); 
		$email_text = str_replace('[rover text]',$machinetext,$email_text); 

		if($emailid == 2){$subject = str_replace('[sub expire days]',$subdays,$emailtemplate->title);} else $subject = $emailtemplate->title;   
	   
		
		$details['logo'] = 'https://ip-rtk-uk.com/images/branding/logos/'.$logo;
		$details['subject'] = $subject;   
	  
		$addresstext = $title;
		$addresstext = $addresstext.','.$address1;  
		if(isset($address2)){$addresstext = $addresstext.','.$address2;}
		if(isset($address3)){$addresstext = $addresstext.','.$address3;}
		$addresstext = $addresstext.','.$towncity;
		$addresstext = $addresstext.','.$county;
		$addresstext = $addresstext.','.$postcode;
	   
	  
		$details['footer'] = $addresstext; 
		$details['text1'] = $email_text;
		
	$this->storeemail($useremail,$subject,$email_text,$sub->business,$sub->dealer,$sub->company,$user['id'],$emailid);
		
	if($emailid == 5){	
		$sub = CasterNtripSubscription::where('rover',$roverid)->first();	
		if($sub->renewsent == 1){
		return;
		} else 
			{
			$sub->renewsent = 1;
			$sub->save();
			$response = $this->sendemail($useremail,$details); //Mail::to($useremail)->send(new SendEmail($details));
			}
			 
	} else $response = $this->sendemail($useremail,$details); //Mail::to($useremail)->send(new SendEmail($details));
		
	// Send email and save copy to dealer contact email 
	$response = $this->sendemail($dealer->email,$details); // Mail::to($dealer->email)->send(new SendEmail($details));	
	$this->storeemail($dealer->email,$subject,$email_text,$sub->business,$sub->dealer,$sub->company,$user['id'],$emailid);	
		
	return $response;
        
   }
	
	public function sendemail($to,$details){
		
		$response = Mail::to($to)->send(new SendEmail($details));
		return $response;
	}
	
	public function resendemail(Request $request){
		
		$emaildata = $request->email;
		$user = $request->user;

		if($emaildata['dealer'] == 0){

					$reseller = CasterBusiness::where('id',$emaildata['reseller'])->first();
					$logo = $reseller->logo;
					$portal = $reseller->logintitle;
					$contact = $reseller->contact;
					$email = $reseller->email;
					$tel = $reseller->tel;
					$title = $reseller->title;
					$address1 = $reseller->address1;
					$address2 = $reseller->address2;
					$address3 = $reseller->address3;
					$towncity = $reseller->towncity;
					$county = $reseller->county;
					$postcode = $reseller->postcode;

		} else {
		
					$dealer = CasterDealer::where('id',$emaildata['dealer'])->first();
					$logo = $dealer->logo;
					$portal = $dealer->logintitle;		
					$contact = $dealer->contact;
					$email = $dealer->email;
					$tel = $dealer->tel;
					$title = $dealer->title;
					$address1 = $dealer->address;
					$address2 = $dealer->address2;
					$address3 = $dealer->address3;
					$towncity = $dealer->towncity;
					$county = $dealer->county;
					$postcode = $dealer->postcode;
					$userdealer = $dealer->id;

			}
		
		$logoimage = '<img src="https://ip-rtk-uk.com/images/branding/logos/'.$logo.'" style="height:40px" alt="Logo">';
		$portalurl = 'https://ip-rtk-uk.com/'.$portal.'/login';								
	
		
		$details['logo'] = 'https://ip-rtk-uk.com/images/branding/logos/'.$logo;
		$details['subject'] = $emaildata['subject'];
		$details['text1'] = $emaildata['text'];	
		
		$addresstext = $title;
		$addresstext = $addresstext.','.$address1;  
		if(isset($address2)){$addresstext = $addresstext.','.$address2;}
		if(isset($address3)){$addresstext = $addresstext.','.$address3;}
		$addresstext = $addresstext.','.$towncity;
		$addresstext = $addresstext.','.$county;
		$addresstext = $addresstext.','.$postcode;
	   
		$details['footer'] = $addresstext; 
	   
    $response = Mail::to($emaildata['sent_email'])->send(new SendEmail($details));
		
	$this->storeemail($emaildata['sent_email'],$emaildata['subject'],$emaildata['text'],$emaildata['reseller'],$emaildata['dealer'],$emaildata['company'],$user['id'],$emaildata['type']);	
   	return $response; 
        
 	}
	
	
	public function testsendmail(Request $request){
		$details['subject'] = 'Rod Test 1';
		$details['text1'] = 'This is a test';
		$details['logo'] = 'cloudbaselogo.png';
		$details['footer'] = 'rb test footer';
			
		
		$response = $this->sendemail($request->email,$details);
		return $response;
	}
	
	
   public function storeemail($emailto,$subject,$content,$reseller,$dealer,$company,$user,$emailid)
    {
		$email = new CasterEmailsLog;
	    $email->title = $subject;
		$email->text = $content;
	    $email->sent_email = $emailto;
	    $email->email_type = $emailid;
	   	$email->reseller = $reseller;
	    $email->dealer = $dealer;
	    $email->company = $company;
	    $email->user = $user;
		$email->save();
		return;
	}	
	
	
   public function index(Request $request)
    {
	   $id = $request->id;
	   $business = $request->business;
	   $dealer = $request->dealer;
	   $company = $request->company;
	   $type = $request->type;
	   $stock = $request->stock;
	   $status=$request->status;
	 //  $expired = $request->expired;
	 //  if($expired == 0){$status2 = 1;$status = 0; } else $status2 = 0;
	   
	  
	   $subs = CasterNtripSubscription::
	   	when($id > 0, function ($q) use($id) {
					return $q->where('id',$id);
			})
		->when($business > 0, function ($q) use($business) {
					return $q->where('business',$business);
				})		
		->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})
		->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})	
		->when($type > 0, function ($q) use($type) {
					return $q->where('type',$type);
			})
		->when($status == 1, function ($q) {
					return $q->where('status','!=',4);		// Filter all subs except EXPIRED
			})	
//		->when($stock != 99, function ($q) use($stock) {
//					return $q->where('stock',$stock);
//			})
		->when($status > 1, function ($q) use($status) {
					return $q->where('status',$status);
			})		
			
		->where('archive',0)
		->orderBy('enddate','asc')	
		->get();
	   
	  
	   if($request->edit){
		   return array('CasterSubs'=>CasterNtripSubscriptionResourceEdit::collection($subs));
	   } else return array('CasterSubs'=>CasterNtripSubscriptionResourceShort::collection($subs));//
    }
	
	public function indexexpiry(Request $request)
    {
	   	$dealer = $request->dealer;
	   	$company = $request->company;
	   	$type = $request->type;
		$start = str_replace('/','-',$request->start);
		$end = str_replace('/','-',$request->end);
	   	$start = date('Y-m-d H:i:s',strtotime($start));
		$end = date('Y-m-d H:i:s',strtotime($end));
		
	   $subs = CasterNtripSubscription::
			when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})
			->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})
			->whereBetween('enddate',[$start,$end])		
			//->where('stock',0)
			->get();
	   
	  		return array('CasterSubs'=>CasterNtripSubscriptionResourceShort::collection($subs));//
    }
	
	 public function getsub(Request $request)
    {
	   $id = $request->id;
	   
	   $subs = CasterNtripSubscription::
	   	where('id',$id)	
		->get();
	   
	   return array('CasterSubs'=>CasterNtripSubscriptionResourceVShort::collection($subs));
    }
	
	
	public function gettotals(Request $request)
    {
		$business = $request->business;	
	   	$dealer = $request->dealer;
	   	$company = $request->company;
	   	$subsarray = [];
	   //if($company > 0){$status = 2;} else $status = 1; // No stock count 	
		$total = 0;
		
		
		$states = CasterSubStatus::where('id','!=',1)->get();
		
		foreach($states as $state){
		
	   	$subs = CasterNtripSubscription::
			when($business > 0, function ($q) use($business) {
					return $q->where('business',$business);
			})
			->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})
			->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})
			->where('status',$state->code)
			->where('rover','!=',0)	
			->get();
		 
		    $subsarray[$state->code]['label'] = $state->message;
		    $subsarray[$state->code]['value'] = count($subs);
			$subsarray[$state->code]['color'] = $state->colour;
			
			if(count($subs) > 0){$total = $total + count($subs);}
		   
	   };
		
		return array('total'=>$total,'data'=>$subsarray);
		
	}
	
	public function getstocksubs(Request $request)
    {
		$reseller = $request->reseller;	
	   	$dealer = $request->dealer;
	   	$company = $request->company;
	   	$subsarray = [];
	  	$subs = CasterSubscription::get();
		$dealersubs = [];
		
		if($request->admin == 1){
			
			foreach($subs as $sub){

						$subslist = CasterNtripSubscription::
							where('business',$reseller)
							->where('type',$sub->type)
							->where('dealer',0)	
							//->where('stock',1)
							->get();

							if(count($subslist) > 0){
								$dealersubs[] = array('value'=>$sub->type,'text'=>$sub->title,'count'=>count($subslist));
							}
						}
			
			
		} else {
		
		$dealers = CasterDealer::
			when($reseller > 0, function ($q) use($reseller) {
					return $q->where('business',$reseller);
			})	
			->get();	
			
			foreach($dealers as $dealer){
				
				$subsarray = [];	

					foreach($subs as $sub){

						$subslist = CasterNtripSubscription::
							where('dealer',$dealer->id)
							->where('type',$sub->type)	
							//->where('stock',1)
							->get();

							if(count($subslist) > 0){
								$subsarray[] = array('value'=>$sub->id,'text'=>$sub->title,'count'=>count($subslist));
							}
						}

				$dealersubs[] = array('id'=>$dealer->id,'dealer'=>$dealer->title,'subs'=>$subsarray);

	   	}
			
		}
		
		return $dealersubs;
		
	
		
		
	}
  
	public function getstocksubsdealer(Request $request)
    {
		
	   	$dealer = $request->dealer;
	   	$subsarray = [];
	  	$subs = CasterSubscription::get();
		$dealersubs = [];
		
			
			foreach($subs as $sub){

						$subslist = CasterNtripSubscription::
							where('type',$sub->type)
							->where('dealer',$dealer)	
							//->where('stock',1)
							->get();

							if(count($subslist) > 0){
								$dealersubs[] = array('value'=>$sub->type,'text'=>$sub->title,'count'=>count($subslist));
							}
						}
	
		return $dealersubs;
		
	}
	
	
	 public function storecancel(Request $request)
    {
		// Put old simm back in stock 
			$simm = CasterSimm::where('id',$request->simm)->first();
			$simm->company = 0;
			$simm->rover = 0;
			$simm->stock = 1;
			$simm->save();
		 
		// Put old sub back in stock 
			$sub = CasterNtripSubscription::where('id',$request->sub)->first();
			$sub->company = 0;
			$sub->rover = 0;
			$sub->stock = 1;
			$sub->startdate = '';
			$sub->enddate = '';
			$sub->active = 0;
			$sub->save();
		 
		//	Update Rover
		 	$rover = Rover::where('id',$request->rover)->first();
			$rover->simm = 0;
			$rover->subscription = 0; 
			$rover->username = '';
			$rover->password = ''; 
			$rover->save();

	
		return;
	}
	
	 public function storestockreseller(Request $request)
    {
		$loop = 0;
		 
		while($loop < $request->number){
		
        $sub = new CasterNtripSubscription;
		$sub->business = $request->reseller;	
		$sub->dealer = 0;
		$sub->stock = 1;
		$sub->user = $request->user;
		$sub->purchase_order = $request->po;	
		$sub->active = 0;
		$sub->type = $request->type;
		$sub->save();
		
		$loop++;	
		}
		
		
		return ;
	}
	
	 public function storestock(Request $request)
    {
		$loop = 0;
		 
		while($loop < $request->number){
		
        $sub = CasterNtripSubscription::
				where('business',$request->reseller)
				->where('dealer',0)	
				//->where('stock',1)
				->where('type',$request->id)
				->first();	
					
		$sub->dealer = $request->dealer;
		$sub->user = $request->user;
		$sub->purchase_order = $request->po;	
		$sub->save();
			
		$loop++;	
		}
		
		
		return;
	}
	
	public function getroversubstotals(Request $request){
		
		$dealer = $request->dealer;
	   	$company = $request->company;
	   	$subsarray = [];
	   $total = 0;
		
		$states = CasterSubStatus::get();

		foreach($states as $state){
		
	   	$subs = CasterNtripSubscription::
				where('status',$state->code)
				->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
				})
				->when($company > 0, function ($q) use($company) {
						return $q->where('company',$company);
				})
				->get();	
		 
		    $subsarray[$state->code]['label'] = $state->message;
		    $subsarray[$state->code]['value'] = count($subs);
			$subsarray[$state->code]['color'] = $state->colour;
			
			if(count($subs) > 0){$total = $total + count($subs);}
		
	   };
		
		return array('total'=>$total,'data'=>$subsarray);
	}

	
	
    public function show($id)
    {
        $sub = CasterNtripSubscription::findorfail($id);
		return new CasterNtripSubscriptionResource($sub);
    }


    public function destroy($id)
    {
        $sub = CasterNtripSubscription::findorfail($id);
		if($sub->delete()){
			return new CasterNtripSubscriptionResource($sub);
		}
    }
	
	public function getsubstatustypes(){
		$states = CasterSubStatus::get();
		return array('status'=>CasterNtripSubscriptionStatusResource::collection($states));//
	}
	
	public function getsubstatustypestotals(Request $request){
		$reseller = $request->business;
		$dealer  = $request->dealer;
		$company = $request->company;
		$expired = $request->expired;
		$type = $request->type;
		$stock = 0;
		$total = 0;
		$totals = [];
		
		//$totals[]  = array('value'=>1,'text'=>'');
		
		if($expired == 1){
	   		$subtypes = CasterSubStatus::select('code','message')->get()->toArray();
			} else $subtypes = CasterSubStatus::select('code','message')->where('code','!=',4)->get()->toArray(); 
		
		foreach(array_slice($subtypes,1) as $subtype){
			
			$subs = CasterNtripSubscription::
			when($reseller > 0, function ($q) use($reseller) {
					return $q->where('business',$reseller);
			})
	   		->when($dealer > 0, function ($q) use($dealer) {
					return $q->where('dealer',$dealer);
			})
			->when($company > 0, function ($q) use($company) {
					return $q->where('company',$company);
			})
			->when($type > 0, function ($q) use($type) {
					return $q->where('type',$type);
			})	
			->where('status',$subtype['code'])	
			//->where('stock',$stock)
			->get();
			
			$count = count($subs);
			$total = $total + $count;
			if($count > 0){
				$string = $subtype['message'].' ('.$count.')';
				$totals[] = array('value'=>$subtype['code'],'text'=>$string);
			}
		}
		
		//$totals[0]['text'] = 'All'.' ('.$total.')';
		
		
		return $totals;
	
	}
	
	public function bm2mlogin(){
		
		$response = Http::post('https://www.commsportal.com/api/sign_in', [
			'username' => 'rod.balgarnie@nickabbey.co.uk',
			'password' => 'DigitalAg1!',
		]);
		
		if(isset($response['usertoken'])){
			return($response['usertoken']);
			} else return null;
		}
	
	public function bm2msetstatus($id,$status){
		
		switch($status){
			case 2:
			$action = 'activate';
			break;
			case 3:
			$action = 'suspend';
			break;
			case 4:
			$action = 'unsuspend';
			break;	
		}
		
		$token = $this->bm2mlogin();
		if($token == null){return 'LOGIN ERROR';}
		
		
		$response = Http::post('https://www.commsportal.com/api/connections/'.$id.'/action', [
			'username' => 'rod.balgarnie@nickabbey.co.uk',
			'usertoken' => $token,
			'name' => $action
		]);
		
		return $response;
		
	}
}
