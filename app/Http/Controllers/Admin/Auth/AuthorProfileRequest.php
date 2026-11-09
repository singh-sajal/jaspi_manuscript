<?php

namespace App\Http\Controllers\Admin\Auth;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class AuthorProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }


    public function rules()
    {

        $uuid = $this->uuid;

        return [
            'name' => 'required|max:30',
            'lname' => 'required|max:30',
            'email' => ['required', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'email', Rule::unique('authors', 'email')->ignore($uuid, 'uuid'),],
            'phone' => ['required', 'numeric', Rule::unique('authors', 'phone')->ignore($uuid, 'uuid'),],
            'state' => 'required',
            'gender' => 'required|in:Male,Female,Other',
            'dob' => 'required',
            'city' => 'required|max:255',
            'pincode' => 'required|numeric|digits:6',
            'address' => 'nullable|max:255'
        ];
    }

    public function messages()
    {
        return [
            'name.max' => 'Name must be less than 30 characters',
            'lname.required' => 'Last name is required',
            'lname.max' => 'Last name must be less than 30 characters',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            failureResponse($validator);
        }

        parent::failedValidation($validator);
    }
}