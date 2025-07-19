<?php
namespace App\Http\Controllers\Admin\Application;


use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class ApplicationRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $commonRules = [
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:word,pdf|max:10240',
            // 'gender' => 'required|in:male,female',
            // 'state' => 'nullable',
            // 'city' => 'nullable',
            // 'address' => 'nullable',
            // 'pincode' => 'nullable',
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
            'title.required' => 'Please provide a title',
            'file.mimes' => 'File should be either in word or pdf format',
            'file.max' => 'File should be less than 10MB',
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

    public function generateCredentials(){
        $uuid = $this->route('application');

    }
}
