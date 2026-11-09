<?php

namespace App\Http\Controllers\Admin\Application;


use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class PublishRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $commonRules = [
            'file_upload' => ['required', 'file', 'max:5120', function ($attribute, $value, $fail) {
                // PHP's finfo magic DB on some servers (e.g. Laragon/Windows)
                // misdetects .docx as application/zip. Laravel's mimes:pdf,docx
                // then rejects valid Word proofs. Accept the file when its
                // detected extension (guessExtension) OR its declared extension
                // resolves to pdf/docx, so legitimate uploads are not blocked.
                if (! $value instanceof UploadedFile) {
                    return;
                }

                $allowed = ['pdf', 'docx'];

                if (in_array(strtolower($value->guessExtension()), $allowed, true)) {
                    return;
                }

                if (in_array(strtolower($value->getClientOriginalExtension()), $allowed, true)) {
                    return;
                }

                $fail('The file upload must be a file of type: pdf, docx.');
            }], // max: 5MB
            'comment' => 'nullable|string|max:2000',
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
            // 'email' => 'required|email|unique:applications,email',
            // 'phone' => 'required|numeric|unique:applications,phone|max_digits:10',


        ]);
    }

    public function update($commonRules)
    {
        $uuid = $this->route('application');
        return array_merge($commonRules, [

            // 'registration_no' => ['required', Rule::unique('applications', 'registration_no')->ignore($uuid, 'uuid'),],

        ]);
    }

    public function generateCredentials()
    {
        $uuid = $this->route('application');
    }
}
