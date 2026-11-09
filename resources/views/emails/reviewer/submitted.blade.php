@component('mail::message')

Dear {{ $editor->name }},

A review has been **submitted** for the following manuscript:

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

**Reviewer:** {{ $reviewer->name ?? '---' }}

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

Please log in to the editorial dashboard to review the submission and proceed with the editorial process in accordance with the journal's policies and guidelines.

**Best regards,**
**JASPI Editorial Team**

@endcomponent