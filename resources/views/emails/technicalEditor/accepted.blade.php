@component('mail::message')

Dear {{ $technicalEditor->name }},

The following manuscript has been **accepted** and has been assigned to you for technical processing:

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

@isset($comment)
**Editorial Comment:**

> {!! $comment !!}
@endisset

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

Please log in to the editorial dashboard to proceed with the technical processing of this manuscript in accordance with the journal's policies and guidelines.

We sincerely appreciate your time and valuable contribution to the editorial process of JASPI.

**Best regards,**
**JASPI Editorial Team**

@endcomponent