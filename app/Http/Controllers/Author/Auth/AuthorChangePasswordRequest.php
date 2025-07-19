<?php

namespace App\Http\Controllers\Author\Auth;



use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class AuthorChangePasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }


    public function rules()
    {

        $commonRules = [

            'old_password' => 'required',
            'password' => [
                'required',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/',
                function ($attribute, $value, $fail) {
                    if (Hash::check($value, authUser('web')->password)) {
                        $fail('The new password must be different from the old password.');
                    }
                },
            ],
            'password_confirmation' => 'required',


        ];

        if ($this->isMethod('POST')) {
            return $this->store($commonRules);
        } elseif ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return $this->update($commonRules);
        }

        return [];
    }

    public function messages()
    {
        return [
            'password.required' => 'Please enter password',
            'password.confirmed' => 'Password and confirm password should be same',
            'password.regex' => 'Password should be at least 6 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character',

        ];
    }
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            failureResponse($validator);
        }

        parent::failedValidation($validator);
    }
    public function store($commonRules)
    {

        return array_merge($commonRules, []);
    }

    public function update($commonRules)
    {

        return array_merge($commonRules, []);
    }
}
