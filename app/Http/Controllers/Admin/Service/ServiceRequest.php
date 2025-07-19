<?php

namespace App\Http\Controllers\Admin\Service;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class ServiceRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $commonRules = [
            'short_description' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
        return [];
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
            'name' => 'required|string|max:255|unique:services,name',
        ]);
    }

    public function update($commonRules)
    {
        $uuid = $this->route('service');
        // Add specific rules for update operation
        return array_merge($commonRules, [

            'name' => "required|string|unique:services,name,{$uuid},uuid",
            // 'name' => ['required', 'string', Rule::unique('services', 'name')->ignore($uuid, 'uuid'),],

        ]);
    }
}
