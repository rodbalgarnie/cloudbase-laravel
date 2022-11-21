<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home/jobs';
	

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
	
	
	
	public function logout() {
		Auth::logout();
		//return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : 'login');
    }
	
	
//	public function vuelogin(Request $request)
//    {
//		
//        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
//        $user = Auth::user();
//			
//		//setcookie("uid", $user->id,0);	
//			
//		// Clear old access tokens for user
//		$tokens = PersonalAccessToken::where('tokenable_id',$user->id)->delete(); 
//			
//		$token = $user->createToken('token-name');
//		//Session::put('token', $token->plainTextToken);	
//		//	
//		//setcookie("token", $token->plainTextToken,0);
//			
//			
//          return response()->json([
//            'status'   => 'ok',
//            'user' 		=> 	$user,
//			'token' 	=> 	$token->plainTextToken
//          ]); 
//        } else { 
//          return response()->json([
//            'status' => 'login error',
//            'user'   => 0,
//			'token' => '' 
//          ]); 
//        } 
//    }

	
	
	
	
}
