@component('mail::message')

Dear {{ $reviewer->name ?? 'Reviewer' }},

Thank you for reviewing the following manuscript. The editorial team has now made a final decision.

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

**Final decision:** {{ $decision }}

@isset($comment)
**Note from the editorial team:**

> {!! $comment !!}
@endif

We are grateful for the time and expertise you contributed to this review. Your input is an essential part of the editorial process and helps us maintain the quality of the journal.

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

Thank you again for your support.

**With appreciation,**
**JASPI Editorial Team**

@endcomponent
