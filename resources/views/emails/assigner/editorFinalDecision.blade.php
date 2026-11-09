@component('mail::message')

Dear {{ $assigner->name }},

The Handling Editor has submitted their **final decision** for the following manuscript assigned to you:

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

**Final Decision:** **{{ $decision }}**

@isset($comment)
**Handling Editor's Feedback:**

> {!! $comment !!}
@endisset

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

Please log in to the editorial dashboard to take the necessary next steps in accordance with the journal's editorial policies and guidelines.

We sincerely appreciate your time and valuable contribution to the editorial process of JASPI.

**Best regards,**
**JASPI Editorial Team**

@endcomponent