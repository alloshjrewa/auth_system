<?php

namespace App\Http\Requests;


use GuzzleHttp\Psr7\Request;

use Illuminate\Validation\ValidationException;
use App\Exceptions\ValidateException;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class SignUpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
       return  true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => 'required|unique:users',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|confirmed:password|min:6',
            'phone_number'  => 'required|unique:users',
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'certificate'   => 'required|mimes:pdf|max:2048' ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);

    }
}
