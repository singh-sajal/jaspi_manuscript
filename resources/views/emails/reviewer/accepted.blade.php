@component('mail::message')

Dear {{ $editor->name }},

A reviewer has **accepted** their assignment for the following manuscript:

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

**Reviewer:** {{ $reviewer->name ?? '---' }}

**Reviewer type:** {{ $slot }}

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

You can follow the review progress from the editorial dashboard.

**Best regards,**
**JASPI Editorial Team**

@endcomponent
