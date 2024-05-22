<?php
namespace App\Traits;



use Illuminate\Http\Request;


trait GeneralTrait {
    public function errorResponse( int $httpResponseCode = 400 ,string $message , ?array $errors = []){
        return response()->json([
            "status" => false ,
            "message" => $message ?? null ,
            "errors"  =>$errors ?? null,
        ] , $httpResponseCode);
    }
    public function SuccessResponse(int $httpResponseCode = 200 , string $message ,    ?array $value = [] ){
        return response()->json([
            "status" => true ,
            "message" => $message ,
            "data"    => $value,
        ] , $httpResponseCode);
    }
}
