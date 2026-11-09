@component('mail::message')

Dear {{ $technicalEditor->name }},

The **formatted proof** for the following manuscript has been uploaded by the publisher and is ready for your typesetting review:

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

@isset($comment)
**Publisher's Comment:**

> {!! $comment !!}
@endisset

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

Please log in to the editorial dashboard to complete the typesetting review in accordance with the journal's policies and guidelines.

**Best regards,**
**JASPI Editorial Team**

@endcomponent