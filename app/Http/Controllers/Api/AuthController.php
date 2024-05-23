<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Events\RegisteredUser;
use App\Enums\TokenAbility;
use App\Traits\FilesTrait;
use App\Traits\ResponseTrait;

use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\DeleteRequest;
use App\Http\Requests\SignUpRequest;
use App\Http\Requests\VerifyEmailRequest;



use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class AuthController extends Controller
{
    use ResponseTrait,FilesTrait;


    public function signupUser(SignUpRequest $request)
    {

        // Photo Path

        $photo_Path = $this->uploadFile($request->file('profile_photo') , '/profile_photos' );

        // certificate Path

        $certificate_Path = $this->uploadFile($request->file('certificate'),'/certificates' );

        //User Register

    $user = User::create([
        'name'                      => $request->name,
        'email'                     => $request->email,
        'password'                  => Hash::make($request->password),
        'phone_number'              => $request->phone_number,
        'profile_photo'             => $photo_Path,
        'certificate'               => $certificate_Path,
        'verification_code'         => Str::random(6),
        'verification_code_expiration' => Carbon::now()->addMinutes(3)
    ]);


    //event(new RegisteredUser($user));

    $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));

    $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

    $value["access_token"] = $accessToken->plainTextToken;

    $value["refresh_token"] = $refreshToken->plainTextToken;


    return $this->SuccessResponse(201 ,"you signed up successfully ,we send a email verification to your email so please verify your email" , $value );

    }

    public function loginUser(LoginRequest $request)
    {

        $user = User::where('email', $request->email)->first();

        //Email Not Found Error Response

        if(!$user){

            return $this->errorResponse(404  ,"Email Does not Exists"  );
        }

        //Password Does Not Match Error Response

        else if ( ! Hash::check($request->password, $user->password) ) {

            return $this->errorResponse(422 ,"Password Does not Match"  );

        }

        //Phone Number Does Not Match Error Response

        else if ( $user->phone_number != $request->phone_number) {

            return $this->errorResponse(422 ,"Phone Number Does not Match"  );
        }

        // Login Success Response

        else {

            $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));
            $value["access_token"] = $accessToken->plainTextToken;
            $value["refresh_token"] = $refreshToken->plainTextToken;

            return $this->SuccessResponse(200 ,"User Logged In Successfully"  , $value  );

        }
    }

    //Logout Process
    public function logoutUser(Request $request)
    {

        $request->user()->tokens()->delete();

        // Logout Success Response
        return $this->SuccessResponse(200 ,"User Logout Successfully" );

    }

    //Refresh Token

    public function refreshToken(Request $request){


        $request->user()->tokens()->delete();

        $accessToken = $request->user()->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));

        $refreshToken = $request->user()->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

        $value['access_token'] = $accessToken->plainTextToken;

        $value['refresh_token'] = $refreshToken->plainTextToken;

        return $this->SuccessResponse(200 ,"Token Generated" , $value );




    }

    //Email Verification

    public function verifyEmail (VerifyEmailRequest $request){

        $user = User::where('verification_code' , '=' , $request->token)->where('id', '=' , $request->id)->first();

        //Code Expired Error Response

            if(Carbon::now() > $user->verification_code_expiration){

                return $this->errorResponse(404 ,"Verification Code Expired"  );

            }

        //Email Verification Success Response

            if(!empty($user && Carbon::now() < $user->verification_code_expiration)){

            $user->email_verified_at = Carbon::now();

            $user->verification_code = null;

            $user->save();

            return $this->SuccessResponse(200 ,"Your Email Verified successfully"  );

            }
    }
    public function destroy (DeleteRequest $request) {
        $password = Hash::make($request->password);
        // Logout Success Response
        $user = User::where( 'email' , '=' , $request->email)->where('password' , '=' ,$password );
        if(empty($user)){
        throw new ModelNotFoundException();
        }
        return $this->SuccessResponse(200 ,"profile_photo Deleted Successfully" );

    }


}

