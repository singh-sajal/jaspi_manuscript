<?php
namespace App\Http\Controllers\Mail;



use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class MailRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $commonRules = [
            'name' => 'required|string|max:255',
            'mail_type' => 'required',
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
            'mail_type.required' => 'Please Select a Mail Type',
            'specialization.required' => 'Please Add a Specialization',
            'registration_no.unique' => 'This Registration No Already Associated With Another Mail',
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
            'email' => 'required|email|unique:mails,email',
            'phone' => 'required|numeric|unique:mails,phone',
            'registration_no' => 'required|unique:mails,registration_no',
            // The titile must be unqieu for given service id

        ]);
    }

    public function update($commonRules)
    {
        $uuid = $this->route('mail');
        return array_merge($commonRules, [

            'email' => ['required', 'email', Rule::unique('mails', 'email')->ignore($uuid, 'uuid'),],
            'phone' => ['required', 'numeric', Rule::unique('mails', 'phone')->ignore($uuid, 'uuid'),],
            'registration_no' => ['required', Rule::unique('mails', 'registration_no')->ignore($uuid, 'uuid'),],

        ]);
    }
}
