@component('mail::message')

Dear {{ $publisher->name }},

The technical editor has **accepted the following manuscript for publication**. It has been assigned back to you to add the DOI and publish it:

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

@if (!empty($comment))
**Technical editor's note:** {{ $comment }}
@endif

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

Please log in to the editorial dashboard to complete the final publication step in accordance with the journal's policies and guidelines.

**Best regards,**
**JASPI Editorial Team**

@endcomponent
