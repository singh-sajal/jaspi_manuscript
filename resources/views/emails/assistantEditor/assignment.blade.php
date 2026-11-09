@component('mail::message')

Dear {{ $assistantEditor->name }},

You have been assigned an application submitted to the Journal of Antimicrobial Stewardship Practices and Infectious Diseases (JASPI). Please review the following details and take the necessary action.

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

@isset($remark)
**Technical Editor Remark:** {{ $remark }}
@endisset

@component('mail::button', ['url' => $url])
Open Applications
@endcomponent

Please log in to the editorial dashboard to proceed with the handling of this manuscript in accordance with the journal's policies and guidelines.

We sincerely appreciate your time and valuable contribution to the editorial process of JASPI.

**Best regards,**
**JASPI Editorial Team**

@endcomponent