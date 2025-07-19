<?php

namespace App\Http\Controllers\Author\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        if ($this->isMethod('get')) {
            return [];
        }

        return [
            'name' => 'required|string|max:255',
            'dob' => 'required|date|before:today',
            'phone' => 'required|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'email' => 'required|email|max:255|unique:authors,email',
            'gender' => 'required|in:Male,Female,Other',
            'country' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'pincode' => 'required|string|regex:/^[0-9]{5,6}$/',
            'terms' => 'required|accepted'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter your full name',
            'name.string' => 'Name must be a valid string',
            'name.max' => 'Name cannot exceed 255 characters',
            'dob.required' => 'Please enter your date of birth',
            'dob.date' => 'Please enter a valid date for date of birth',
            'dob.before' => 'Date of birth must be a date before today',
            'phone.required' => 'Please enter your phone number',
            'phone.regex' => 'Please enter a valid phone number',
            'phone.min' => 'Phone number must be at least 10 digits',
            'email.required' => 'Please enter your email address',
            'email.email' => 'Please enter a valid email address',
            'email.max' => 'Email cannot exceed 255 characters',
            'email.unique' => 'This email is already registered',
            'gender.required' => 'Please select your gender',
            'gender.in' => 'Please select a valid gender option',
            'country.required' => 'Please enter your country',
            'country.string' => 'Country must be a valid string',
            'country.max' => 'Country name cannot exceed 100 characters',
            'state.required' => 'Please enter your state',
            'state.string' => 'State must be a valid string',
            'state.max' => 'State name cannot exceed 100 characters',
            'city.required' => 'Please enter your city',
            'city.string' => 'City must be a valid string',
            'city.max' => 'City name cannot exceed 100 characters',
            'address.required' => 'Please enter your address',
            'address.string' => 'Address must be a valid string',
            'address.max' => 'Address cannot exceed 255 characters',
            'pincode.required' => 'Please enter your pincode',
            'pincode.regex' => 'Please enter a valid pincode (5-6 digits)',
            'terms.required' => 'You must accept the terms and conditions',
            'terms.accepted' => 'You must accept the terms and conditions'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}