@component('mail::message')

Dear {{ $technicalEditor->name }},

A **revised manuscript** has been re-submitted by the author and is ready for your technical re-check:

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

@isset($comment)
**Author's Comment:**

> {!! $comment !!}
@endisset

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

Please log in to the editorial dashboard to perform the technical checks and record your decision in accordance with the journal's policies and guidelines.

**Best regards,**
**JASPI Editorial Team**

@endcomponent