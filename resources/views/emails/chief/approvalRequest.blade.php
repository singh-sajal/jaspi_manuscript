@component('mail::message')

Dear {{ $chief->name }},

You have received a **request for final approval** regarding a manuscript submitted to the Journal of Antimicrobial Stewardship Practices and Infectious Diseases (JASPI).

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

**Requested by:** {{ $requester->name ?? '---' }}

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

Please log in to the editorial dashboard to review the manuscript and grant or reject your approval in accordance with the journal's editorial policies and guidelines.

We sincerely appreciate your time and valuable contribution to the editorial process of JASPI.

**Best regards,**
**JASPI Editorial Team**

@endcomponent