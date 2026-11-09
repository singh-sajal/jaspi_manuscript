@component('mail::message')

Dear {{ $editor->name }},

You have been invited to serve as the Handling Editor for the following manuscript submitted to the Journal of Antimicrobial Stewardship Practices and Infectious Diseases (JASPI):

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

@isset($assignedBy)
**Assigned by:** {{ $assignedBy->name ?? '---' }}
@endisset

@component('mail::button', ['url' => $url])
Open Manuscript
@endcomponent

We kindly request you to review the manuscript and, upon accepting the assignment, proceed with the editorial process in accordance with the journal's editorial policies and guidelines.

**As timely editorial decisions are important to authors, we would appreciate receiving your final editorial comments/recommendation within 21 days of accepting this invitation.**

If you are unable to handle this manuscript, please decline the assignment through the editorial system and provide an appropriate reason.

Before forwarding the manuscript for further editorial processing, please ensure that it has been appropriately assessed and handled in accordance with the journal's guidelines.

**Confidentiality:** The manuscript, this invitation, and all aspects of the peer-review and editorial process must be treated as strictly confidential. The manuscript or any information related to its review must not be shared with or disclosed to anyone without prior authorization from the Editor-in-Chief and the authors, as applicable. This confidentiality obligation continues even after publication of the manuscript.

We sincerely appreciate your time and valuable contribution to the editorial process of JASPI.

**Best regards,**
**JASPI Editorial Team**

@endcomponent
