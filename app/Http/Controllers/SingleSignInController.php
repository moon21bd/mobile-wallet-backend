<?php

namespace App\Http\Controllers;

use Encore\Admin\Facades\Admin;
use App\Repositories\TokenHandler;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SingleSignInController extends Controller
{
 
    public function ssoValidation()
    {
		$validator = Validator::make(request()->all(), [
            'username' => 'required|string',
            'root_ref_id' => 'required|integer']);
		if($validator->fails()){
			return response()->json(['error_code' => '01', 'error_message' => 'login name or ref_id missing'], 200);            
        }
        $username = request()->get('username');
		$root_ref_id = request()->get('root_ref_id');
		$userModel = config('admin.database.users_model');
		$user = $userModel::where(['active_status' => 1, 'root_ref_id' => $root_ref_id])->first();
		if ($user && ($username == $user->username)) {
			$ss_token = TokenHandler::createToken($user->username);
			$url = config('app.url') . '/web/sso-login/' . $ss_token;
			return response()->json(['status' => 'success', 'url' => $url], 200);
		} else {
			return response()->json(['error_code' => '00', 'error_message' => 'User not exist'], 200);
		}
    }
	
	public function ssoLogin($token)
    {
        if ($token) {
            $dycryptedValue = TokenHandler::extractToken($token);
            $time = Carbon::parse($dycryptedValue->created);
            $totalDuration = $time->diffInSeconds(Carbon::now(), false);
            if ($totalDuration <= 60) {
				$userModel = config('admin.database.users_model');                
                $user = $userModel::where('username', $dycryptedValue->user)->first();
                if (!empty($user)) {
                    $this->guard()->loginUsingId($user->id);
                    return redirect(config('app.url').'/admin');
                }
            }
            return redirect('/admin/auth/login');
        } else {
            return redirect('/admin/auth/login');
        }
    }
	
	/**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Admin::guard();
    }
	
}
