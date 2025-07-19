<?php

namespace App\Http\Controllers\Admin\Checklist;


use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class ChecklistRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $commonRules = [
            'type' => 'required|string|max:255',
            'service_id' => 'required',
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
            'service_id.required' => 'Please Select a Service',
            'type.required' => 'Please Select a Checklist Type',
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
            // The titile must be unqieu for given service id
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('checklists', 'title')->where(function ($query) {
                    return $query->where('service_id', request('service_id'));
                }),
            ],
        ]);
    }

    public function update($commonRules)
    {
        $uuid = $this->route('checklist');
        return array_merge($commonRules, [

            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('checklists', 'title')->where(function ($query) {
                    return $query->where('service_id', request('service_id'));
                })->ignore($uuid, 'uuid'), // Ignore the current record's UUID
            ],

        ]);
    }
}
