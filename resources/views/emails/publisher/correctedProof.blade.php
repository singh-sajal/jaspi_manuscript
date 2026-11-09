@component('mail::message')

Dear {{ $publisher->name }},

The author has **submitted the corrected proof** for the following manuscript:

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

Please log in to the editorial dashboard to review the corrected proof and proceed with the final publication steps in accordance with the journal's policies and guidelines.

**Best regards,**
**JASPI Editorial Team**

@endcomponent