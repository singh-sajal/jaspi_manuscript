@component('mail::message')

Dear {{ $requester->name }},

The **Editor-in-Chief** has responded to your approval request for the following manuscript:

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

**Chief's Decision:** **{{ $decision }}**

@isset($response)
**Chief's Response:**

> {!! $response !!}
@endisset

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

Please log in to the editorial dashboard to take the necessary next steps in accordance with the journal's editorial policies and guidelines.

**Best regards,**
**JASPI Editorial Team**

@endcomponent