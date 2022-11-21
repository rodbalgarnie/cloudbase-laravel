<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use Auth;

class User extends Authenticatable implements Auditable
{
   	use \OwenIt\Auditing\Auditable;
	use HasApiTokens,Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fname', 'lname','email', 'password',
    ];
   
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
	
	
	public function resellers()
	{	
		return $this->hasOne('App\CasterBusiness', 'id', 'business')->withDefault(['title' => '-']);
	}
	
	public function dealers()
	{	
		return $this->hasOne('App\CasterDealer', 'id', 'dealer')->withDefault(['title' => '-']);
	}
	
	public function companies()
	{	
		return $this->hasOne('App\CasterCompany', 'id', 'company')->withDefault(['title' => '-']);	
	}
	
	public function roles()
	{	
		return $this->hasOne('App\Role', 'id', 'role')->withDefault(['title' => '-']);	
	}
	
	public function readonly(){
		if($this->readonly == 0){return '-';} else return 'Yes';
	}
	
	
	public function sendPasswordResetNotification($token)
	{
    $this->notify(new \App\Notifications\MailResetPasswordNotification($token));
	}
	

}
