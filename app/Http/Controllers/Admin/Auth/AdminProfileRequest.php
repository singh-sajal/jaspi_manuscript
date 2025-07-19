<?php

namespace App\Http\Controllers\Admin\Auth;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class AdminProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }


    public function rules()
    {

        $commonRules = [
            'name' => 'required|max:30',

            'state' => 'required',
            'gender' => 'required|in:Male,Female',
            'dob' => 'required',
            'city' => 'required|max:255',
            'pincode' => 'required|numeric|digits:6',
            'address' => 'nullable|max:255'

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
            'name.max' => 'Name must be less than 30 characters',
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

        $uuid = $this->uuid;
        return array_merge($commonRules, [

            'email' => ['required', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'email', Rule::unique('admins', 'email')->ignore($uuid, 'uuid'),],
            'phone' => ['required', 'numeric', Rule::unique('admins', 'phone')->ignore($uuid, 'uuid'),],

        ]);
    }

    public function update($commonRules)
    {

        return array_merge($commonRules, []);
    }
}
