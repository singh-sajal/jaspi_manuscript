<?php

namespace App\Http\Controllers\Author\Application;


use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class ApplicationRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $commonRules = [

            // 'submission_type' => 'required|in:new_submission,revised_submission',
            'author_affiliation' => 'required|max:100|min:3',
            // 'author_orcid_id' => 'required|max:16|min:16|size:16|regex:/^(?=.*[0-9])[a-zA-Z0-9]{16}$/',
            'author_orcid_id' => 'required|size:19|regex:/^[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}$/',
            'author_saspi_id' => 'nullable|max:50|min:3',
            '​description' => [
                'nullable',
                function ($attribute, $value, $fail) {

                    $plain = trim(strip_tags($value)); // remove HTML tags

                    $length = mb_strlen($plain); // safe for UTF-8

                    if ($length < 10) {
                        $fail('The ' . $attribute . ' must be at least 10 characters.');
                    }

                    if ($length > 10000) {
                        $fail('The ' . $attribute . ' must not exceed 1000 characters.');
                    }
                },
            ],

            'short_title' => [
                'required',
                'string',
                // Custom logic to count words
                function ($attribute, $value, $fail) {
                    if (str_word_count($value) > 6) {
                        $fail('The ' . $attribute . ' must be less than 6 words.');
                    }
                },
            ],
            // 'keywords' => 'required|string|min:1',
            'keywords' => [
                'required',
                'string',
                'regex:/^(\S+\s+){4,}\S+$/'
            ],


            // 'article_type' => ['required', 'array', 'min:1'],
            // 'article_type.*' => ['string'],
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
            'title.required' => 'The title is required.',
            'title.string' => 'The title must be a valid string.',
            'title.max' => 'The title must not exceed 255 characters.',

            'submission_type.required' => 'The submission type is required.',
            'submission_type.in' => 'The submission type must be either "new_submission" or "revised_submission".',

            'author_affiliation.required' => 'The author affiliation is required.',
            'author_affiliation.max' => 'The author affiliation must not exceed 100 characters.',
            'author_affiliation.min' => 'The author affiliation must be at least 3 characters.',

            'author_orcid_id.required' => 'The author ORCID ID is required.',
            'author_orcid_id.regex' => 'The reference must be 16 characters and contain at least one number.',
            'author_orcid_id.max' => 'The author ORCID ID must not exceed 16 characters.',
            'author_orcid_id.min' => 'The author ORCID ID must be at least 16 characters.',
            'author_orcid_id.size' => 'The author ORCID ID must be exactly 16 characters.',


            'author_saspi_id.required' => 'The author SASPI ID is required.',
            'author_saspi_id.max' => 'The author SASPI ID must not exceed 50 characters.',
            'author_saspi_id.min' => 'The author SASPI ID must be at least 3 characters.',

            'description.required' => 'The abstract is required.',
            'description.max' => 'The abstract must not exceed 1000 characters.',
            'description.min' => 'The abstract must be at least 10 characters.',


            'keywords.regex' => 'Keywords must contain at least 5 words.',

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
            'title' => 'required|string|max:255',
        ]);
    }

    public function update($commonRules)
    {
        $uuid = $this->route('application');
        return array_merge($commonRules, [
            'title' => [
                'required',
                'string',
                'max:255',
                // Rule::unique('applications', 'title')->ignore($uuid),
            ],
        ]);
    }

    public function generateCredentials()
    {
        $uuid = $this->route('application');
    }
}
