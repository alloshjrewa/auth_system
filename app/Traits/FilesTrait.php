<?php
namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


trait FilesTrait {

    public function uploadFile(UploadedFile $file, $path, $disk = '')
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs($path, $filename, $disk);

        return $filename;
    }

    public function DeleteFile( int $httpResponseCode = 400 ,string $message ,?array $errors = [] ){

        return response()->json([
            "status" => false ,
            "message" => $message ?? null ,
            "errors"  =>$errors ?? null,
        ] , $httpResponseCode);

    }

}
