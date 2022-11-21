<?php
namespace App\Http\Controllers;
use App\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;

use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
	use SendsPasswordResetEmails;
    
	public function vuelogin(Request $request)
    {
		
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password,'archive'=>0])){ 
        $user = Auth::user();
		if($user->role !== $request->role){
			 return response()->json([
            'status' => 'login error',
            'user'   => 0,
			'token' => '' 
          ]); 
		}		
			
		$token = $user->createToken('token-name');
			
          return response()->json([
            'status'   => 'ok',
            'user' 		=> 	$user,
			'token' 	=> 	$token->plainTextToken
          ]); 
        } else { 
          return response()->json([
            'status' => 'login error',
            'user'   => 0,
			'token' => '' 
          ]); 
        } 
    }
	
	
    public function logout()
    {
        $this->guard()->logout();
        return response()->json([
            'status' => 'success',
            'msg' => 'Logged out Successfully.'
        ], 200);
    }
	
	
    public function user(Request $request)
    {
        $user = User::find(Auth::user()->id);
        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }
	
    public function refresh()
    {
        if ($token = $this->guard()->refresh()) {
            return response()
                ->json(['status' => 'successs'], 200)
                ->header('Authorization', $token);
        }
        return response()->json(['error' => 'refresh_token_error'], 401);
    }
	
    private function guard()
    {
        return Auth::guard();
    }
	
	public function callResetPassword(Request $request)
	{
		return $this->reset($request);
	}
	
	public function reset(Request $request)
    {
       		$input = $request->only('email','token', 'password', 'password_confirmation');
			$validator = Validator::make($input, [
				'token' => 'required',
				'email' => 'required|email',
				'password' => 'required|confirmed|min:8',
			]);
			if ($validator->fails()) {
				return response()->json($validator->errors());
			}
			$response = Password::reset($input, function ($user, $password) {
				$user->password = Hash::make($password);
				$user->save();
			});
			$message = $response == Password::PASSWORD_RESET ? 'Password reset successfully' : 'Unknown Error';
			return response()->json($message);
	}
	
	protected function resetPassword($user, $password)
	{
    $user->password = Hash::make($password);
    $user->save();
    event(new PasswordReset($user));
	}
	
	public function sendPasswordResetLink(Request $request)
	{
    return $this->sendResetLinkEmail($request);
	}
	
	protected function sendResetLinkResponse(Request $request, $response)
	{
    return response()->json([
        'message' => 'Password reset email sent.',
        'data' => $response
		]);
	}
	
	protected function sendResetLinkFailedResponse(Request $request, $response)
	{
    return response()->json(['message' => 'Email could not be sent to this email address.']);
	}
	
	protected function sendResetResponse(Request $request, $response)
	{
    return response()->json(['message' => 'Password reset successfully.']);
	}
	
	protected function sendResetFailedResponse(Request $request, $response)
	{
    return response()->json(['message' => 'Failed, Invalid Token.']);
	}
}