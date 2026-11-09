@component('mail::message')

Dear {{ $publisher->name }},

The following manuscript has been **accepted** and assigned to you for the publication process:

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

Please log in to the editorial dashboard to proceed with the publication processing of this manuscript in accordance with the journal's policies and guidelines.

We sincerely appreciate your time and valuable contribution to the publication process of JASPI.

**Best regards,**
**JASPI Editorial Team**

@endcomponent