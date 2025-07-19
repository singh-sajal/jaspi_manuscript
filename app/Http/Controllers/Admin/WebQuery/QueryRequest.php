<?php

namespace App\Http\Controllers\Admin\WebQuery;


use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class QueryRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $commonRules = [
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|file|mimes:png,jpg|max:1024',
            'gender' => 'required|in:Male,Female',
            'state' => 'nullable',
            'city' => 'nullable',
            'address' => 'nullable',
            'pincode' => 'nullable',
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
            'author_type.required' => 'Please Select a Author Type',
            'specialization.required' => 'Please Add a Specialization',
            'registration_no.unique' => 'This Registration No Already Associated With Another Author',
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
            'email' => 'required|email|unique:authors,email',
            'phone' => 'required|numeric|unique:authors,phone|max_digits:10',


        ]);
    }

    public function update($commonRules)
    {
        $uuid = $this->route('author');
        return array_merge($commonRules, [

            'email' => ['required', 'email', Rule::unique('authors', 'email')->ignore($uuid, 'uuid'),],
            'phone' => ['required', 'numeric', Rule::unique('authors', 'phone')->ignore($uuid, 'uuid'),],
            // 'registration_no' => ['required', Rule::unique('authors', 'registration_no')->ignore($uuid, 'uuid'),],

        ]);
    }

    public function generateCredentials(){
        $uuid = $this->route('author');

    }
}
