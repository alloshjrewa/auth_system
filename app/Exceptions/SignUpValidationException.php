<?php

namespace App\Exceptions;

use Exception;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\SignUpRequest;

class SignUpValidationException extends Exception
{
    use GeneralTrait;


    public function render(Request $request ,?array $errors =[])
    {
            return $this->errorResponse( 422 , 'Validation Error'  ,$errors);
    }


}
