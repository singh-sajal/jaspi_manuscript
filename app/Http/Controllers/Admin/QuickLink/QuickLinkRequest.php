<?php

namespace App\Http\Controllers\Admin\QuickLink;


use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class QuickLinkRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $commonRules = [
            'name' => 'required|string|max:255',
            'url' => 'required',
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
            'url.required' => 'Please enter url',
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
            'name' => [
                'unique:quick_links,name',
            ],
            'url' => 'unique:quick_links,url',
        ]);
    }

    public function update($commonRules)
    {
        $uuid = $this->route('quicklink');
        return array_merge($commonRules, [

            'name' => [

                Rule::unique('quick_links', 'name')->ignore($uuid, 'uuid'), // Ignore the current record's UUID
            ],

            'url' => [
                Rule::unique('quick_links', 'url')->ignore($uuid, 'uuid'), // Ignore the current record's UUID
            ],
        ]);
    }
}
