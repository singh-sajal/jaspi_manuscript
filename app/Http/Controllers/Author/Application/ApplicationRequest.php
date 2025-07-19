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

            'submission_type' => 'required|in:new_submission,revised_submission',
            'author_affiliation' => 'required|max:100|min:3',
            'author_orcid_id' => 'required|max:50|min:3',
            'author_saspi_id' => 'nullable|max:50|min:3',
            'description' => 'nullable|max:250|min:10',
            'article_type' => ['required', 'array', 'min:1'],
            'article_type.*' => ['string'],
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
            'author_orcid_id.max' => 'The author ORCID ID must not exceed 50 characters.',
            'author_orcid_id.min' => 'The author ORCID ID must be at least 3 characters.',

            'author_saspi_id.required' => 'The author SASPI ID is required.',
            'author_saspi_id.max' => 'The author SASPI ID must not exceed 50 characters.',
            'author_saspi_id.min' => 'The author SASPI ID must be at least 3 characters.',

            'description.required' => 'The description is required.',
            'description.max' => 'The description must not exceed 250 characters.',
            'description.min' => 'The description must be at least 10 characters.',
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
