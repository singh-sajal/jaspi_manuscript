<?php

namespace App\Http\Controllers\Admin\Integration;


use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class IntegrationRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $commonRules = [
            'name' => 'required|string|max:255',
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
            'name.required' => 'Please enter name',
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
            'name' => [
                'unique:integrations,name',
            ],
        ]);
    }

    public function update($commonRules)
    {
        $id = $this->route('integration');
        return array_merge($commonRules, [
            'name' => [
                Rule::unique('integrations', 'name')->ignore($id),
            ],
        ]);
    }
}