<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;
	
    public function __construct($details)
    {
        $this->details = $details;
    }
  
	public function build()
    {
	    return $this->markdown('mail.subscription.subscriptiontemplate')->subject($this->details['subject'])->with('details', $this->details);
   }
	

}
