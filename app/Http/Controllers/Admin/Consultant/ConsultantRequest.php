<?php

namespace App\Http\Controllers\Admin\Consultant;


use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class ConsultantRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $commonRules = [
            'name' => 'required|string|max:255',
            'consultant_type' => 'required',
            'specialization' => 'required',
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
            'consultant_type.required' => 'Please Select a Consultant Type',
            'specialization.required' => 'Please Add a Specialization',
            'registration_no.unique' => 'This Registration No Already Associated With Another Consultant',
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
            'email' => 'required|email|unique:consultants,email',
            'phone' => 'required|numeric|unique:consultants,phone',
            'registration_no' => 'required|unique:consultants,registration_no',
            // The titile must be unqieu for given service id

        ]);
    }

    public function update($commonRules)
    {
        $uuid = $this->route('consultant');
        return array_merge($commonRules, [

            'email' => ['required', 'email', Rule::unique('consultants', 'email')->ignore($uuid, 'uuid'),],
            'phone' => ['required', 'numeric', Rule::unique('consultants', 'phone')->ignore($uuid, 'uuid'),],
            'registration_no' => ['required', Rule::unique('consultants', 'registration_no')->ignore($uuid, 'uuid'),],

        ]);
    }
}
