@component('mail::message')

Dear {{ $editor->name ?? 'Handling Editor' }},

A reviewer assigned to the manuscript below has **passed their review deadline** and has not yet submitted a review.

**Title:** {{ $application->title ?? 'Untitled' }}

**Manuscript ID:** {{ $application->application_id ?? '---' }}

**Reviewer:** {{ $reviewer->name ?? '---' }}@if ($reviewer?->email) ({{ $reviewer->email }})@endif

**Review type:** {{ ucfirst($assignment->type ?? 'internal') }}

**Assigned on:** {{ optional($assignment->assigned_at)->format('d-M-Y') ?? '---' }}

**Deadline passed on:** {{ optional($assignment->timeout_at)->format('d-M-Y') ?? '---' }}

The reviewer has **not** been unassigned. Please follow up with them or reassign the review as appropriate.

@component('mail::button', ['url' => $url])
View Manuscript
@endcomponent

**Best regards,**
**JASPI Editorial Team**

@endcomponent
