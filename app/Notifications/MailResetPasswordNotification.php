<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\User;

class MailResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token) 
    {
         $this->token = $token;
    }

  
    public function via($notifiable) 
    {
        return ['mail'];
    }

   
  public function toMail( $notifiable ){
	   
  	$details['url'] = url( "/password/reset/". $this->token."?email=".urlencode($notifiable->email));
	$user = User::with('dealers')->where('email',$notifiable->email)->get();
	$company = $user[0]['dealers'];
	  
	$details['logo'] = 'https://ip-rtk-uk.com/images/logos/'.$company->logo;
	$details['companyuser'] = $company->contact;
	$details['company'] = $company->title;
	$details['companyemail'] = $company->email;
	$details['companytel'] = $company->tel;  
	  
	$details['salutation'] =  "Hi ".$user[0]['fname'].",";
	$details['text1'] = "You are receiving this email because we received a password reset request for your account.This password reset link will expire in 60 minutes.If you did not request a password reset, no further action is required.";
	  
	$details['text2'] = 'If you’re having trouble clicking the "Reset Password" button, click the link below or copy and paste the URL below into your web browser:';
	  
	  
	$address = $company->title;
	$address = $address.','.$company->address1;  
	if($company->address2 !== ''){$address = $address.','.$company->address2;}
	if($company->address3 !== ''){$address = $address.','.$company->address3;}
	$address = $address.','.$company->towncity;
	$address = $address.','.$company->county;
	$address = $address.','.$company->postcode;
	  
	$details['address'] = $address;  
	  
	return (new MailMessage)
                ->subject('Password Reset Link')
                ->markdown('mail.password.reset', ['details' => $details]);
	  
   }
    

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
