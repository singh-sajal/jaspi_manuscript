@component('mail::message')

Dear {{ $editor->name }},

A reviewer has **declined** their assignment for the following manuscript:

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

**Reviewer:** {{ $reviewer->name ?? '---' }}

@isset($reason)
**Reason for declining:**

> {!! $reason !!}
@endisset

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

Please log in to the editorial dashboard to assign a replacement reviewer so that the review process can continue without delay.

**Best regards,**
**JASPI Editorial Team**

@endcomponent