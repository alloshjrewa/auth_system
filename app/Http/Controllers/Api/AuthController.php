<?php

namespace App\Http\Controllers\Api;




use Carbon\Carbon;
use App\Models\User;

use App\Enums\TokenAbility;
use Illuminate\Support\Str;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use App\Events\RegisteredUser;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\SignUpRequest;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\VerifyEmailRequest;
use Illuminate\Database\Events\QueryExecuted;

class AuthController extends Controller
{
    use GeneralTrait;


    public function signupUser(SignUpRequest $request)
    {
        $photo = $request->file('profile_photo');
        $photo_name = $photo->getClientOriginalName();
        $photo_Path = Storage::putFileAs('/profile_photos' , $photo, $photo_name);

    // certificate Path

        $certificate = $request->file('certificate');
        $certificate_name = $certificate->getClientOriginalName();
        $certificate_Path = Storage::putFileAs('/certificates' , $certificate, $certificate_name);

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

   // event(new RegisteredUser($user));

    $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
    $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));
    $value["access_token"] = $accessToken->plainTextToken;
    $value["refresh_token"] = $refreshToken->plainTextToken;
    return $this->SuccessResponse(201 ,"you signed up successfully ,we send a email verification to your email so please verify your email" , $value );

    }

    public function loginUser(LoginRequest $request)
    {

        $user = User::where('email', $request->email)->first();

        if(!$user){
            return $this->errorResponse(404  ,"Email Does not Exists"  );
        }

        else if ( ! Hash::check($request->password, $user->password) ) {
            return $this->errorResponse(422 ,"Password Does not Match"  );
        }
        else if ( $user->phone_number != $request->phone_number) {
            return $this->errorResponse(422 ,"Phone Number Does not Match"  );
        }
        else {
            $token =$user->createToken("API TOKEN", [''],$expiresAt = now()->addMinute(5))->plainTextToken;
            return $this->SuccessResponse(200 ,"User Logged In Successfully"  , $token  );

        }
    }

    //Logout Process
    public function logoutUser(Request $request)
    {
        $user = Auth::user();
        if(!$user){
            return $this->errorResponse(400,"UnAuthenticated User"  );
            return $this->errorResponse(401  ,"UnAuthenticated User"  );
        }
        else {

        $request->user()->tokens()->delete();
        return $this->returnSuccessResponse(200 ,"User Logout Successfully" ,"" , ''  );

        }
    }

    //Refresh Token

    public function refreshToken(Request $request){

        if(Carbon::now() > $request->user()->currentAccessToken()->expires_at){
            return $this->errorResponse(404 ,"Refresh Token Expired"  );

        }else{

        $request->user()->tokens()->delete();
        $accessToken = $request->user()->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        $refreshToken = $request->user()->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));
            $value['ac_token'] = $accessToken->plainTextToken;
            $value['rf_token'] = $refreshToken->plainTextToken;

        return $this->SuccessResponse(200 ,"Token Generated" , $value );


        }

    }

    //Email Verification

    public function verifyEmail (VerifyEmailRequest $request){

        $user = User::where('verification_code' , '=' , $request->token)->where('id', '=' , $request->id)->first();
        if(Carbon::now() > $user->verification_code_expiration){
            return  response()->json([
                "status"    => "false",
                "message"   => "Email Verification Expired"
            ], 422);
           }

            if(!empty($user && Carbon::now() < $user->verification_code_expiration)){
            $user->email_verified_at = Carbon::now();
            $user->verification_code = null;
            $user->save();
            return response()->json([
            "status"    => "true",
            "message"   => "Your Email Verified successfully"
        ], 200);
            }


    }


}

