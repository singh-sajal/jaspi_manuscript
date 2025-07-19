<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Traits\HasRequestHelpers;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AdminAuthRequest extends FormRequest
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
                'username' => 'required',
                'password' => 'required',
            ];
        }
    }

    public function messages()
    {
        return [
            'username.required' => 'Please enter valid email or username',
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
