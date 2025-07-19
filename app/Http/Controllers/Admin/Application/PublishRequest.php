<?php

namespace App\Http\Controllers\Admin\Application;


use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class PublishRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $commonRules = [
            'final_script' => 'required|file|mimes:pdf,docx|max:5120', // max: 5MB
            'comment' => 'nullable|string|max:2000',
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
        return array_merge($commonRules, [
            // 'email' => 'required|email|unique:applications,email',
            // 'phone' => 'required|numeric|unique:applications,phone|max_digits:10',


        ]);
    }

    public function update($commonRules)
    {
        $uuid = $this->route('application');
        return array_merge($commonRules, [

            // 'registration_no' => ['required', Rule::unique('applications', 'registration_no')->ignore($uuid, 'uuid'),],

        ]);
    }

    public function generateCredentials()
    {
        $uuid = $this->route('application');
    }
}
