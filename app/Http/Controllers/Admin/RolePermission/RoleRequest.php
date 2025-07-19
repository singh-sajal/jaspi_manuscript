<?php

namespace App\Http\Controllers\Admin\RolePermission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class RoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $commonRules = [
            'name' => ['required', 'string', 'max:255', 'not_regex:/^superadmin$/i'],
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
            'name.required' => 'Please Enter Role Name',
            'name.not_regex' => 'Opps! Role is reserved',
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
        $commonRules['name'][] = 'unique:roles,name';
        return array_merge($commonRules, []);
    }

    public function update($commonRules)
    {
        $commonRules['name'][] = 'unique:roles,name,' . $this->route('role');
        return array_merge($commonRules, []);
    }
}
