<?php

namespace App\Http\Controllers\Api;




use App\Models\User;

use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Catch_;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\SignUpRequest;
use Illuminate\Database\QueryException;
use App\Http\Requests\VerifyEmailRequest;
use Illuminate\Database\Events\QueryExecuted;
use App\Traits\GeneralTrait;

class AuthController extends Controller
{
    use GeneralTrait;


    public function signupUser(SignUpRequest $request)
    {
            $user = $this->SignupProcess($request);

            $this->SignupCreateTokenProcess($user);

    }

    public function loginUser(LoginRequest $request)
    {

        $user = User::where('email', $request->email)->first();

            $this->LoginProcess($user , $request);

    }

    //Logout Process
    public function logoutUser(Request $request)
    {
        $this->LogoutProcess($request);

    }

    //Refresh Token

    public function refreshToken(Request $request){

        $true_or_false = $this->TokenCheckProcess($request->user()->currentAccessToken());

        if($true_or_false){

        $this->refreshTokenProcess($request);

        }
    }

    //Email Verification

    public function verifyEmail (VerifyEmailRequest $request){

        $user = User::where('verification_code' , '=' , $request->token)->where('id', '=' , $request->id)->first();
        $this->verifyEmailProcess($user);

     }
}

