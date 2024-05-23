<?php
namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


trait ResponseTrait {
    public function errorResponse( int $httpResponseCode = 400 ,string $message ,?array $errors = [] ){

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
    public function ValidationException(ValidationException $e): JsonResponse
    {
        $errors = $e->validator->errors()->toArray();
        return response()->json([
            'message' => 'Validation Error',
            'errors'  => $errors,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function NotFoundException(NotFoundHttpException $e): JsonResponse
    {
        return response()->json([
            'Message' => 'Not Found',
            'errors' => $e->getMessage() ,
        ], 404);
    }
    public function ApiAuthException(NotFoundHttpException $e): JsonResponse
    {
        return response()->json([
            'Message' => 'Url Not Found , it should start with api/auth',
            'errors' => $e->getMessage(),
        ], 404);
    }

    public function AuthenticationException(AuthenticationException $e): JsonResponse
    {
        return response()->json([
            'Message' => 'Un Authenticated , You Shoul\'d be Authenticated',
            'errors' => $e ,
        ], 401);
    }
    public function ModelNotFoundException(ModelNotFoundException $e): JsonResponse
    {
        return response()->json([
            'Message' => 'this Model Does Not Exists',
            'errors' => get_class($e),
        ], 404);
    }
    public function ConfliectException(ModelNotFoundException $e): JsonResponse
    {
        return response()->json([
            'Message' => 'Sorry , This Data Exists Before',
            'errors' => $e,
        ], 409);
    }



}
