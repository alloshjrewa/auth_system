<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required',
            'token' => 'required|max:6|min:6'
        ];
    }

    protected function failedValidation(Validator $validator)
{
    $errors = $validator->errors();

    $response = response()->json([
        'message' => 'Validation Error',
        'details' => $errors->messages(),
    ], 422);

    throw new HttpResponseException($response);
    }
}
