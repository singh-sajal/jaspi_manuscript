<?php
namespace App\Http\Controllers\Author\Auth;



use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class AuthorAvatarRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }


    public function rules()
    {



        if ($this->isMethod('POST')) {
            return [
                'avatar' => 'required|file|mimes:jpeg,png,jpg|max:2048',
            ];
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
}
