<?php

namespace App\Http\Controllers\Staff;


use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class StaffRequest extends FormRequest
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
            // 'role_id' => 'required'
            // 'staff_type' => 'required',
            // 'specialization' => 'required',
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
            'staff_type.required' => 'Please Select a Staff Type',
            'specialization.required' => 'Please Add a Specialization',
            'registration_no.unique' => 'This Registration No Already Associated With Another Staff',
            'email.unique' => 'This email is already taken, use another email',
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
            'email' => 'required|email|unique:admins,email',
            'phone' => 'required|numeric|unique:admins,phone|digits:10',
            // 'registration_no' => 'required|unique:staffs,registration_no',
            // The titile must be unqieu for given service id

        ]);
    }

    public function update($commonRules)
    {
        $uuid = $this->route('staff');
        return array_merge($commonRules, [

            'email' => ['required', 'email', Rule::unique('admins', 'email')->ignore($uuid, 'uuid'),],
            'phone' => ['required', 'numeric', Rule::unique('admins', 'phone')->ignore($uuid, 'uuid'),],
            // 'registration_no' => ['required', Rule::unique('staffs', 'registration_no')->ignore($uuid, 'uuid'),],

        ]);
    }

    public function generateCredentials(){
        $uuid = $this->route('staff');

    }
}
