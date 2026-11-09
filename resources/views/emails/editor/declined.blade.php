@component('mail::message')

Dear {{ $assigner->name }},

The Handling Editor assigned to the following manuscript has **declined the assignment**:

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

**Handling Editor:** {{ $editor->name ?? '---' }}

@isset($reason)
**Reason for declining:**

> {!! $reason !!}
@endisset

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

Please log in to the editorial dashboard to assign a new Handling Editor for this manuscript so that the editorial process can continue.

**Best regards,**
**JASPI Editorial Team**

@endcomponent