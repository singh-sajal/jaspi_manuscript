<?php

namespace App\Http\Controllers\Author\Auth;

use App\Traits\HasRequestHelpers;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthorAuthRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }
    public function rules()
    {

        if ($this->isMethod('get')) {
            return [];
        } else {
            return [
                'email' => 'nullable|email',
                // 'contactperson' => 'required|max:255|min:2',
                // 'phone' => 'nullable|numeric',
                // 'state' => 'required',
                // 'name' => 'required|unique:organizations,name|max:255|regex:/^[A-Za-z0-9\s]+$/',
                // 'address' => 'nullable|max:255',
                // 'loginemail' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/|unique:users,email',
                'password' => 'required|max:20|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/',
            ];
        }
    }

    public function messages()
    {
        return [
            'name.unique' => 'Company is Already registered',
            'loginemail.unique' => 'Login email has already been taken'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            failureResponse($validator);
            $this->failureResponse($validator);
        }

        parent::failedValidation($validator);
    }
}
