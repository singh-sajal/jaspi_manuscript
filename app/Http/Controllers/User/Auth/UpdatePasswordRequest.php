<?php

namespace App\Http\Controllers\Admin\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePasswordRequest extends FormRequest
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
                'oldpw' => 'required',
                'password' => [
                    'required',
                    'confirmed',
                    'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/',
                    function ($attribute, $value, $fail) {
                        if (Hash::check($value, auth()->user()->password)) {
                            $fail('The new password must be different from the old password.');
                        }
                    },
                ],
                'password_confirmation' => 'required',
            ];
        }
    }

    public function messages()
    {
        return [

            'password.regex' => 'The password must be at least 6 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).'

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'status' => '400',
                    'errors' => $validator->errors(),
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }
}
