<?php
namespace App\Traits;

use Carbon\Carbon;
use App\Models\User;
use App\Enums\TokenAbility;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\RegisteredUser;
use Illuminate\Support\Facades\Hash;

use App\Http\Requests\SignUpRequest;
use App\Http\Requests\LoginRequest;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Exceptions\HttpResponseException;

trait GeneralTrait {

    //                                              *** Signup User Process ***

    public function SignupProcess(SignUpRequest $request){

    // Profile Photo Path

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

    event(new RegisteredUser($user));

    return $user;

    }

    //                                                  *** Create Token Process ***

    public function SignupCreateTokenProcess(User $user){
        $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

            $response =  response()->json([
                "status"    => "true",
                "message"   => "you signed up successfully ,we send a email verification to your email so please verify your email",
                "token"     => $accessToken->plainTextToken,
                "refreshToken" => $refreshToken->plainTextToken

            ], 201);
            throw new HttpResponseException($response);

    }

            //                                                  *** Login User Process ***
    public function LoginProcess( $user , LoginRequest $request){

        if(!$user){
            $response = response()->json([
                'status' => 'false',
                'message' => 'query Error',
                'error'   => 'Email Does not Exists'
            ]);
            throw new HttpResponseException($response);


        }
        if ( ! Hash::check($request->password, $user->password) || $user->phone_number != $request->phone_number) {

            $response = response()->json([
                "status"    => "false",
                "message"   => "data inserted not found or does not match"
            ], 404);
            throw new HttpResponseException($response);

        }else {

        $response =  response()->json([
            "status"    => "true",
            "message"   => "User Logged In Successfully",
            "token"     => $user->createToken("API TOKEN", [''],$expiresAt = now()->addMinute(5))->plainTextToken
        ], 201);
        throw new HttpResponseException($response);
        }
    }
    //                                                  *** Lougout Process ***
        public function LogoutProcess(Request $request){

            $request->user()->tokens()->delete();

            $response =  response()->json([
                "status"    => "true",
                "message"   => "User Logout Successfully",
            ], 200);

            throw new HttpResponseException($response);

        }

    //                                                  *** Refresh Token Check Process ***
    public function TokenCheckProcess($token) : bool{
        if(Carbon::now() > $token->expires_at){
            $response = response()->json([
                'message' => "Refresh Token Expired"],401);

            throw new HttpResponseException($response);
                return false;

    }else{
        return true;
    }
}
//                                                  *** Create Access Token Process After Refresh Request ***
    public function refreshTokenProcess(Request $request){
        $request->user()->tokens()->delete();
        $accessToken = $request->user()->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        $response = response()->json(['message' => "Token generated",
        'token' => $accessToken->plainTextToken],201);
        throw new HttpResponseException($response);

    }

    //                                                  *** Email Verification Process ***
    public function verifyEmailProcess(User $user){

        if(Carbon::now() > $user->verification_code_expiration){
            $response =  response()->json([
                "status"    => "false",
                "message"   => "Email Verification Expired"
            ], 422);
            throw new HttpResponseException($response);
           }
            if(!empty($user && Carbon::now() < $user->verification_code_expiration)){
            $user->email_verified_at = Carbon::now();
            $user->verification_code = null;
            $user->save();
            $response = response()->json([
            "status"    => "true",
            "message"   => "Your Email Verified successfully"
        ], 200);
            }
            throw new HttpResponseException($response);

    }
}
