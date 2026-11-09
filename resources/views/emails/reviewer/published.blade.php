@component('mail::message')

Dear {{ $reviewer->name ?? 'Reviewer' }},

Thank you for reviewing the following manuscript. We are pleased to let you know that it has now been published.

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

@if ($doiUrl)
**DOI:** [{{ $doi }}]({{ $doiUrl }})
@elseif ($doi)
**DOI:** {{ $doi }}
@endif

Your review played an important part in reaching this outcome, and we sincerely appreciate the time and expertise you contributed.

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

Thank you again for supporting the journal.

**With appreciation,**
**JASPI Editorial Team**

@endcomponent
