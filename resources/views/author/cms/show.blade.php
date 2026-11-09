 @php
     $status = $application->status;
     $reviseCount = $application->timelines->where('status', 'revise')->count();

 @endphp
 @extends('author.app')
 @section('title', 'Manuscript Details')
 @section('breadcrumb', 'Manuscript Details')
 @section('page-title')
     Manuscript Details:
 @endsection
 @section('breadcrumb-button')

     {{-- @if ($status == 'revise' || $status == 'editor-revise')
         <a class="btn btn-group btn-primary"
             href="{{ route('author.application.show', $application->uuid) }}#manuscript">Re-upload Manuscript</a>
     @endif --}}

     @if ($status == 'incomplete')
         <a class="btn btn-sm btn-primary btn-label"
             href="{{ route('author.application.create', [
                 'applicationId' => $application->uuid,
                 'step' => 'preview',
             ]) }}">
             <i class="ri-edit-box-line" style="font-size: large"></i>
         </a>
     @endif
     @if ($status == 'revise')
         <a class="btn btn-sm btn-primary btn-label"
             href="{{ route('author.application.create', [
                 'applicationId' => $application->uuid,
                 'step' => 'documents',
             ]) }}">
             {{-- <i class="ri-edit-box-line" style="font-size: large"></i> --}}
             Revise
         </a>
     @endif


 @endsection
 @section('css')

     <style>
         .file-box {
             border: 1px solid #ccc;
             padding: 15px;
             /* width: 200px; */
             text-align: center;
             border-radius: 8px;
         }

         .file-icon {
             font-size: 40px;
             margin-bottom: 10px;
         }

         #current-step,
         #attachments,
         #manuscript {
             scroll-margin-top: 100px;
         }
     </style>

 @endsection
 @section('content')
     <div class="offcanvas offcanvas-end" tabindex="-1" style="z-index: 10000;width:80%" id="FileOffcanvas"
         data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="FileOffcanvasLabel"></div>
     <div class="modal" id="AjaxModal" tabindex="-1"></div>
     <div class="card">
         <div class="card-body">
             <div class="row">
                 @if ($status === 'proofreading')
                     @include('author.cms.partials.proofreading-step')
                 @endif

                 <div class="col-7">
                     <div class="card">
                         <div class="card-header d-flex align-items-center justify-content-between">
                             <h4 class="card-title">
                                 {{ $application->application_id ? '#' . $application->application_id : '' }}</h4>

                             @if (in_array($application->status, [
                                     'editor-accepts',
                                     'editor-rejects',
                                     'editor-revise',
                                     'chief-approval',
                                     'chief-review',
                                     'chief-response',
                                     'under review',
                                     'assigning reviewers',
                                     'reviewed',
                                 ]))
                                 <span class="badge badge-sm bg-dark">Processing</span>
                             @else
                                 <span class="badge badge-sm bg-dark">{{ ucfirst($application->status) }}</span>
                             @endif
                         </div>
                         <div class="card-body">
                             <div class="text-muted w-100 table-responsive">
                                 <div class="table-card">
                                     <table class="w-100 mb-0 table">
                                         <tbody>
                                             <tr>
                                                 <td class="fw-semibold">Title</td>
                                                 <td>{{ $application->title ?? 'N/A' }}</td>
                                             </tr>
                                             <tr>
                                                 <td class="fw-semibold">Submission Type</td>
                                                 <td>{{ !empty($application->submission_type)
                                                     ? ($application->submission_type == 'new_submission'
                                                         ? 'New Submission'
                                                         : 'Revised Submission')
                                                     : 'N/A' }}
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td class="fw-semibold">Author Affiliation</td>
                                                 <td>{{ $application->author_affiliation ?? 'N/A' }}</td>
                                             </tr>

                                             <tr>
                                                 <td class="fw-semibold">ORCID ID</td>
                                                 <td>{{ $application->author_orcid_id ?? 'N/A' }}</td>
                                             </tr>
                                             <tr>
                                                 <td class="fw-semibold">SASPI Membership Number</td>
                                                 <td>{{ $application->author_saspi_id ?? 'N/A' }}</td>
                                             </tr>
                                             <tr>
                                                 <td class="fw-semibold">Article Type</td>
                                                 <td>{{ !empty($application->article_type) ? implode(',', $application->article_type ?? []) : 'N/A' }}
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <td class="fw-semibold">Article Check</td>
                                                 <td>

                                                     @php
                                                         $article_checks = [
                                                             //  'Plagiarism Check',
                                                             'Cross reference Check',
                                                         ];

                                                         $savedArticleChecks = $application->article_check ?? [];

                                                         foreach ($article_checks as $check) {
                                                             echo $check .
                                                                 ': ' .
                                                                 (in_array($check, $savedArticleChecks)
                                                                     ? 'yes'
                                                                     : 'no') .
                                                                 ', &nbsp;';
                                                         }
                                                     @endphp
                                                 </td>
                                             </tr>



                                         </tbody>
                                     </table>
                                     <!--end table-->
                                 </div>
                             </div>


                         </div>
                     </div>
                 </div>
                 @if ($status !== 'incomplete')

                     <div class="col-5">

                         {{-- Timeline Column --}}
                         <div class="card">
                             <div class="mt-3">
                                 <div id="DZ_W_TimeLine02" class="widget-timeline dz-scroll style-1 height370 p-3">
                                     <ul class="timeline">
                                         @php
                                             $color = [
                                                 'primary',
                                                 'info',
                                                 'success',
                                                 'danger',
                                                 'warning',
                                                 'purple',
                                                 'secondary',
                                             ];

// Map: author-visible status -> the hidden handling-editor status whose
                                              // decision comment the "read more" link should surface
                                              $editorReadMoreMap = [
                                                  'accepted' => ['editor-accepts'],
                                                  'revise'   => ['editor-revise'],
                                                  'rejected' => ['editor-rejects'],
                                              ];
                                         @endphp

                                         @forelse ($authorTimelines as $timeline)
@php
                                                  $editorTimeline = null;

                                                  if (isset($editorReadMoreMap[$timeline->status])) {
                                                      // Look in the FULL (unfiltered) timeline history, not $authorTimelines,
                                                      // since editor-* rows are excluded from $authorTimelines itself.
                                                      $editorTimeline = $application->timelines
                                                          ->where('created_at', '<=', $timeline->created_at)
                                                          ->whereIn('status', $editorReadMoreMap[$timeline->status])
                                                          ->sortByDesc('created_at')
                                                          ->first();
                                                  }
                                              @endphp

                                             @if ($timeline->status == 'revise' || $timeline->status == 'out of scope' || $timeline->status == 'proofreading' || $timeline->status == 'pre-accepted')
                                                 <li class="fade-in">
                                                     <div class="timeline-badge {{ $color[$loop->index % 7] }}"></div>
                                                     <a class="timeline-panel text-muted">
                                                         <span>{{ $timeline->created_at->diffForHumans() }}</span>
                                                         <h6 class="mb-0">Status: <strong
                                                                 class="text-primary">{{ ucfirst($timeline->status) }}</strong>
                                                         </h6>
                                                          <p class="mb-0">
                                                              Remark: {{ $timeline->remark }}

@if ($timeline->status == 'revise')
                                                                 @if (!empty($timeline->data['comment']))
                                                                     <span class="text-primary actionHandler"
                                                                         data-url="{{ route('admin.application.techEditorReadMore', ['uuid' => $application->uuid, 'timeline_id' => $timeline->id]) }}"
                                                                         style="cursor: pointer"><strong>read more</strong></span>
                                                                 @elseif (!isset($timeline->data['checks']) && $editorTimeline)
                                                                     <span class="text-primary actionHandler"
                                                                         data-url="{{ route('admin.application.readMistake', ['uuid' => $application->uuid, 'timeline_id' => $editorTimeline->id]) }}"
                                                                         style="cursor: pointer"><strong>read more</strong></span>
                                                                 @endif
                                                             @endif

                                                             @if (in_array($timeline->status, ['out of scope', 'proofreading', 'pre-accepted']) && !empty($timeline->data['comment']))
                                                                 <span class="text-primary actionHandler"
                                                                     data-url="{{ route('admin.application.techEditorReadMore', [
                                                                         'uuid' => $application->uuid,
                                                                         'timeline_id' => $timeline->id,
                                                                     ]) }}"
                                                                     style="cursor: pointer"><strong>read
                                                                         more</strong></span>
                                                             @endif
                                                         </p>
                                                     </a>
                                                 </li>
                                             @elseif (!empty($timeline->data) && $timeline->status == 'processing')
                                                 <li class="fade-in">
                                                     <div class="timeline-badge {{ $color[$loop->index % 7] }}"></div>
                                                     <a class="timeline-panel text-muted">
                                                         <span>{{ $timeline->created_at->diffForHumans() }}</span>
                                                         <h6 class="mb-0">Status: <strong
                                                                 class="text-primary">{{ ucfirst($timeline->status) }}</strong>
                                                         </h6>
                                                         <p class="mb-0">Remark: {{ $timeline->remark }}
                                                             <span class="text-primary actionHandler"
                                                                 data-url="{{ route('author.application.authorUpdate', ['uuid' => $application->uuid, 'timeline_id' => $timeline->id]) }}"
                                                                 style="cursor: pointer"><strong>read more</strong></span>
                                                         </p>
                                                     </a>
                                                 </li>
                                             @elseif ($timeline->status == 'accepted')
                                                 <li class="fade-in">
                                                     <div class="timeline-badge {{ $color[$loop->index % 7] }}"></div>
                                                     <a class="timeline-panel text-muted">
                                                         <span>{{ $timeline->created_at->diffForHumans() }}</span>
                                                         <h6 class="mb-0">Status: <strong class="text-primary">Online
                                                                 First/Typesetting Review</strong></h6>
                                                         <p class="mb-0">
                                                             Remark: {{ $timeline->remark }}
                                                             @if (!empty($timeline->data['comment']))
                                                                 <span class="text-primary actionHandler"
                                                                     data-url="{{ route('admin.application.techEditorReadMore', ['uuid' => $application->uuid, 'timeline_id' => $timeline->id]) }}"
                                                                     style="cursor: pointer"><strong>read more</strong></span>
                                                             @elseif ($editorTimeline)
                                                                 <span class="text-primary actionHandler"
                                                                     data-url="{{ route('admin.application.readMistake', ['uuid' => $application->uuid, 'timeline_id' => $editorTimeline->id]) }}"
                                                                     style="cursor: pointer"><strong>read more</strong></span>
                                                             @endif
                                                         </p>
                                                     </a>
                                                 </li>
                                             @elseif ($timeline->status == 'rejected')
                                                 <li class="fade-in">
                                                     <div class="timeline-badge {{ $color[$loop->index % 7] }}"></div>
                                                     <a class="timeline-panel text-muted">
                                                         <span>{{ $timeline->created_at->diffForHumans() }}</span>
                                                         <h6 class="mb-0">Status: <strong
                                                                 class="text-primary">{{ ucfirst($timeline->status) }}</strong>
                                                         </h6>
<p class="mb-0">
                                                              Remark: {{ $timeline->remark }}
                                                              @if (!empty($timeline->data['comment']))
                                                                  <span class="text-primary actionHandler"
                                                                      data-url="{{ route('admin.application.techEditorReadMore', ['uuid' => $application->uuid, 'timeline_id' => $timeline->id]) }}"
                                                                      style="cursor: pointer"><strong>read more</strong></span>
                                                              @elseif ($editorTimeline)
                                                                  <span class="text-primary actionHandler"
                                                                      data-url="{{ route('admin.application.readMistake', ['uuid' => $application->uuid, 'timeline_id' => $editorTimeline->id]) }}"
                                                                      style="cursor: pointer"><strong>read more</strong></span>
                                                              @endif
                                                          </p>
                                                      </a>
                                                  </li>
                                              @else
                                                 <li class="fade-in">
                                                     <div class="timeline-badge {{ $color[$loop->index % 7] }}"></div>
                                                     <a class="timeline-panel text-muted">
                                                         <span>{{ $timeline->created_at->diffForHumans() }}</span>
                                                         <h6 class="mb-0">Status: <strong
                                                                 class="text-primary">{{ ucfirst($timeline->status) }}</strong>
                                                         </h6>
                                                         <p class="mb-0">Remark: {{ $timeline->remark }}</p>
                                                         @if ($timeline->admin)
                                                             <p class="mb-0">Handled by:
                                                                 <strong>{{ $timeline->admin->name }}</strong></p>
                                                         @endif
                                                     </a>
                                                 </li>
                                             @endif
                                         @empty
                                             <p>Timeline not available</p>
                                         @endforelse
                                     </ul>
                                 </div>
                             </div>
                         </div>
                     </div>
                 @endif


                 <div class="{{ $application->status == 'incomplete' ? 'col-5' : 'col-12' }}">
                     <div class="card h-100">
                         <div class="card-header py-3 d-flex align-items-center justify-content-between">
                             <h5 class="card-title mb-0">Co-Authors</h5>
                             {{-- @if ($application->status == 'submitted' || $application->status == 'processing')
                 <button class="btn btn-sm btn-secondary actionHandler"
                     data-url="{{ route('author.application.co_author', $application->uuid) }}">
                     <i class="ri-add-circle-line" style="font-size: large"></i>
                 </button>
             @endif --}}
                         </div>

                         <div class="card-body p-0">
                             <div class="table-responsive">
                                 <table class="table table-hover table-striped align-middle mb-0">
                                     <thead class="table-light text-muted">
                                         <tr>
                                             <th scope="col" class="ps-3">Name</th>
                                             <th scope="col">Email</th>
                                             <th scope="col">Phone</th>
                                             <th scope="col">Affiliation</th>
                                             <th scope="col">Orcid ID</th>
                                             <th scope="col">Saspi ID</th>
                                             {{-- <th scope="col">Actions</th> --}}
                                         </tr>
                                     </thead>
                                     <tbody>
                                         @forelse ($application->co_author_data ?? [] as $coAuthor)
                                             <tr id="co-author-{{ $coAuthor['coauthor_id'] }}">
                                                 <td class="ps-3 fw-medium">{{ $coAuthor['coauthor_name'] }}</td>
                                                 <td>
                                                     <span class="text-nowrap font-monospace text-primary">
                                                         {{ $coAuthor['coauthor_email'] }}
                                                     </span>
                                                 </td>
                                                 <td>{{ $coAuthor['coauthor_phone'] }}</td>
                                                 <td>{{ $coAuthor['coauthor_affiliation'] ?? 'N/A' }}</td>
                                                 <td>{{ $coAuthor['coauthor_orcid_id'] ?? 'N/A' }}</td>
                                                 <td>{{ $coAuthor['coauthor_saspi_id'] ?? 'N/A' }}</td>
                                                 {{--
                                <td class="text-center">
                                     @if ($application->status == 'submitted' || $application->status == 'processing')
                                         <div class="brg-group btn-group-sm">
                                             <a class="text-info border-end actionHandler px-1"
                                                data-url="{{ route('author.application.co_author_update', ['uuid' => $application->uuid, 'author_id' => $coAuthor['coauthor_id']]) }}">
                                                 <i class="ri-edit-box-line" style="font-size: large"></i>
                                             </a>
                                             <a class="fs-12 text-danger btn-danger actionHandler px-1"
                                                data-action="delete"
                                                data-url="{{ route('author.application.co_author_delete', ['uuid' => $application->uuid, 'author_id' => $coAuthor['coauthor_id']]) }}">
                                                 <i class="ri-delete-bin-line" style="font-size: large"></i>
                                             </a>
                                         </div>
                                     @endif
                                </td>
                                --}}
                                             </tr>
                                         @empty
                                             <tr>
                                                 <td colspan="6" class="text-center py-4 text-muted">
                                                     <div class="d-flex flex-column align-items-center">
                                                         <i class="ri-user-unfollow-line fs-24 mb-2 opacity-50"></i>
                                                         <span>No Co-Authors Added.</span>
                                                     </div>
                                                 </td>
                                             </tr>
                                         @endforelse
                                     </tbody>
                                 </table>
                             </div>
                         </div>
                     </div>
                 </div>

                 <div class="col-12 mt-3">
                     <div class="card h-100">
                         <div class="card-header py-3">
                             <h5 class="card-title mb-0">Reviewer Suggestions</h5>
                         </div>

                         <div class="card-body p-0">
                             <div class="table-responsive">
                                 <table class="table table-hover table-striped align-middle mb-0">
                                     <thead class="table-light text-muted">
                                         <tr>
                                             {{-- <th scope="col" class="ps-3">Title</th> --}}
                                             <th scope="col">Name</th>
                                             {{-- <th scope="col">Last Name</th> --}}
                                             <th scope="col">Email</th>
                                             <th scope="col">Institution</th>
                                             <th scope="col">Specialization</th>
                                             <th scope="col">Recommendation</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                         @forelse($application->data['reviewer_suggestions'] ?? [] as $reviewer)
                                             <tr>
                                                 {{-- <td class="ps-3">{{ $reviewer['title'] ?? '-' }}</td> --}}
                                                 <td class="fw-medium text-nowrap">
                                                     {{ $reviewer['first_name'] . ' ' . $reviewer['last_name'] ?? '-' }}
                                                 </td>

                                                 <td>
                                                     <span class="text-nowrap font-monospace text-primary">
                                                         {{ $reviewer['email'] ?? '-' }}
                                                     </span>
                                                 </td>
                                                 <td>{{ $reviewer['institution'] ?? '-' }}</td>
                                                 <td>
                                                     <span class="text-dark">
                                                         {{ $reviewer['specialization'] ?? '-' }}
                                                     </span>
                                                 </td>
                                                 <td>
                                                     {{-- FIX: Added max-width and text-break to constrain the floating text --}}
                                                     {{-- <div style="max-width: 250px;" class="text-break text-wrap"> --}}
                                                     {{-- Changed text-active to text-muted for consistency --}}
                                                     {{-- <small class="text-muted"> --}}
                                                     {{ $reviewer['recommended'] ?? '-' }}
                                                     {{-- </small> --}}
                                                     {{-- </div> --}}
                                                 </td>
                                             </tr>
                                         @empty
                                             <tr>
                                                 <td colspan="7" class="text-center py-4 text-muted">
                                                     <div class="d-flex flex-column align-items-center">
                                                         <i class="ri-user-unfollow-line fs-24 mb-2 opacity-50"></i>
                                                         <span>No reviewer suggestions added yet.</span>
                                                     </div>
                                                 </td>
                                             </tr>
                                         @endforelse
                                     </tbody>
                                 </table>
                             </div>
                         </div>
                     </div>
                 </div>


                 <div class="col-12">
                     <div class="card-body">

                         <p class="fw-semibold">Abstract</p>
                         <div>

                             {!! $application->description ?? '' !!}
                         </div>
                     </div>
                 </div>

                 <div class="col-12">
                     <div class="card mt-2" id="attachments">

                         {{-- MAIN HEADER --}}
                         <div class="card-header">
                             <h4 class="card-title">Attachments</h4>
                         </div>

                         {{-- ========================= --}}
                         {{-- OTHER ATTACHMENTS ROW --}}
                         {{-- ========================= --}}
                         <div class="card-body">
                             <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">

                                 @php $counter = 1; @endphp

                                 @foreach ($other_attachment_categories as $category)
                                     @php
                                         $file = $otherFiles
                                             ->where('attachment_type', $category)
                                             ->sortByDesc('created_at')
                                             ->first();
                                     @endphp

                                     <div class="card flex-grow-1" style="min-width:260px">

                                         {{-- Header --}}
                                         <div class="card-header d-flex align-items-center">
                                             <div class="d-flex align-items-center justify-content-center bg-dark rounded-circle"
                                                 style="height:1.3rem;width:1.3rem;font-size:10px;">
                                                 <span class="text-white">{{ $counter++ }}</span>
                                             </div>

                                             <h4 class="card-title flex-grow-1 ms-1" style="font-size:12px;">
                                                 {{ $category }}
                                                 <span class="required"></span>
                                             </h4>
                                         </div>

                                         {{-- Body --}}
                                         <div class="card-body">
                                             <div class="d-flex align-items-center">

                                                 {{-- STATUS ICON --}}
                                                 <span
                                                     class="avatar avatar-lg {{ $file ? 'bg-success' : 'bg-danger' }}
                                      rounded-circle d-flex align-items-center justify-content-center">
                                                     <i
                                                         class="{{ $file ? 'ri-check-line' : 'ri-file-2-line' }} fs-20 text-white"></i>
                                                 </span>

                                                 {{-- CONTENT --}}
                                                 <div class="d-flex flex-column border-start ms-2 px-2">

                                                     @if ($file)
                                                         <p class="fw-semibold fs-12 mb-0 ms-1">
                                                             <i>Uploaded On: {{ $file->created_at->format('d-m-Y') }}</i>
                                                         </p>

                                                         <div class="d-flex gap-1">
                                                             <span
                                                                 class="fs-12 text-dark text-decoration-underline actionHandler px-1"
                                                                 data-url="{{ route('file.viewframe') }}"
                                                                 data-frame="offcanvas" data-handler="openFilePreview"
                                                                 data-file-url="{{ getFileUrl($file->file_url) }}">
                                                                 View
                                                             </span>


                                                             {{-- <a href="#"
                                                                 class="actionHandler fs-12 text-info text-decoration-underline border-start px-1"
                                                                 data-url="{{ route('author.application.file_upload', [
                                                                     'id' => $file->id,
                                                                     'type' => $category,
                                                                     'app_id' => $application->id,
                                                                 ]) }}">
                                                                 Replace
                                                             </a> --}}

                                                             {{-- <a href="#"
                                                                 class="fs-12 text-danger actionHandler border-start px-1"
                                                                 data-action="delete"
                                                                 data-url="{{ route('author.application.file_destroy', [
                                                                     'app_id' => $application->uuid,
                                                                     'id' => $file->id,
                                                                 ]) }}">
                                                                 <i class="ri-delete-bin-line"></i>
                                                             </a> --}}
                                                         </div>
                                                     @else
                                                         <p class="fw-semibold fs-12 mb-0">No File Uploaded yet</p>

                                                         <a href="#"
                                                             class="actionHandler fs-12 text-info text-decoration-underline"
                                                             data-url="{{ route('author.application.file_upload', [
                                                                 'id' => 'na',
                                                                 'type' => $category,
                                                                 'app_id' => $application->id,
                                                             ]) }}">
                                                             Upload
                                                         </a>
                                                     @endif

                                                 </div>
                                             </div>
                                         </div>

                                     </div>
                                 @endforeach

                             </div>
                         </div>

                         {{-- ========================= --}}
                         {{-- UPLOAD MANUSCRIPT ROW --}}
                         {{-- ========================= --}}
                         <div class="card-body border-top" id="manuscript">
                             <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">

                                 @php
                                     $counter = 1;
                                     $latestManuscript = $manuScriptFiles->first();
                                 @endphp

                                 @foreach ($manuScriptFiles as $file)
                                     <div class="card flex-grow-1" style="min-width:260px">

                                         {{-- HEADER --}}
                                         <div class="card-header d-flex align-items-center">

                                             {{-- NUMBER --}}
                                             <div class="d-flex align-items-center justify-content-center bg-dark rounded-circle"
                                                 style="height:1.3rem;width:1.3rem;font-size:10px;">
                                                 <span class="text-white">{{ $counter++ }}</span>
                                             </div>

                                             {{-- TITLE --}}
                                             <h4 class="card-title flex-grow-1 ms-1" style="font-size:12px;">
                                                 Manuscript
                                                 <span class="required"></span>
                                                 {{-- ✅ LATEST / HISTORY BADGE --}}
                                                 {{-- @dump($file->id ) --}}
                                                 @if ($file->id === $latestManuscript->id)
                                                     <span class="badge bg-success ms-1" style="font-size:9px;">
                                                         Latest
                                                     </span>
                                                 @else
                                                     <span class="badge bg-secondary ms-1" style="font-size:9px;">
                                                         History
                                                     </span>
                                                 @endif
                                             </h4>
                                         </div>

                                         {{-- BODY --}}
                                         <div class="card-body">
                                             <div class="d-flex align-items-center">

                                                 {{-- STATUS ICON --}}
                                                 <span
                                                     class="avatar avatar-lg bg-success rounded-circle d-flex align-items-center justify-content-center">
                                                     <i class="ri-check-line fs-20 text-white"></i>
                                                 </span>

                                                 {{-- CONTENT --}}
                                                 <div class="d-flex flex-column border-start ms-2 px-2">
                                                     <p class="fw-semibold fs-12 mb-0 ms-1">
                                                         <i>Uploaded On: {{ $file->created_at->format('d-m-Y') }}</i>
                                                     </p>

                                                     <div class="d-flex gap-1">

                                                         {{-- VIEW --}}
                                                         {{-- <span
                                                             class="fs-12 text-dark text-decoration-underline actionHandler px-1"
                                                             data-handler="openFilePreview"
                                                             data-file-url="{{ getFileUrl($file->file_url) }}">
                                                             View
                                                         </span> --}}
                                                         <span
                                                             class="fs-12 text-dark text-decoration-underline actionHandler px-1"
                                                             data-url="{{ route('file.viewframe') }}"
                                                             data-frame="offcanvas" data-handler="openFilePreview"
                                                             data-file-url="{{ getFileUrl($file->file_url) }}">
                                                             View
                                                         </span>

                                                         {{-- REPLACE (ONLY FOR LATEST) --}}
                                                         {{-- @if ($file->id === $latestManuscript->id)
                                                             <a href="#"
                                                                 class="actionHandler fs-12 text-info text-decoration-underline border-start px-1"
                                                                 data-url="{{ route('author.application.file_upload', [
                                                                     'id' => $file->id,
                                                                     'type' => 'Upload Manuscript',
                                                                     'app_id' => $application->id,
                                                                 ]) }}">
                                                                 Replace
                                                             </a>
                                                         @endif --}}

                                                         {{-- DELETE --}}
                                                         {{-- <a href="#"
                                                             class="fs-12 text-danger actionHandler border-start px-1"
                                                             data-action="delete"
                                                             data-url="{{ route('author.application.file_destroy', [
                                                                 'app_id' => $application->uuid,
                                                                 'id' => $file->id,
                                                             ]) }}">
                                                             <i class="ri-delete-bin-line"></i>
                                                         </a> --}}

                                                     </div>
                                                 </div>
                                             </div>
                                         </div>

                                     </div>
                                 @endforeach

                             </div>
                         </div>

                         {{-- ========================= --}}
                         {{-- UPLOAD Proofread ROW --}}
                         {{-- ========================= --}}
                         <div class="card-body border-top">
                             <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">

                                 @php
                                     $counter = 1;
                                     $latestManuscript = $correctedFiles->first();
                                 @endphp

                                 {{-- EXISTING LOOP --}}
                                 @foreach ($correctedFiles as $file)
                                     <div class="card flex-grow-1" style="min-width:260px">

                                         {{-- HEADER --}}
                                         <div class="card-header d-flex align-items-center">

                                             {{-- NUMBER --}}
                                             <div class="d-flex align-items-center justify-content-center bg-dark rounded-circle"
                                                 style="height:1.3rem;width:1.3rem;font-size:10px;">
                                                 <span class="text-white">{{ $counter++ }}</span>
                                             </div>

                                             {{-- TITLE --}}
                                             <h4 class="card-title flex-grow-1 ms-1 mb-0" style="font-size:12px;">
                                                 Corrected File
                                                 <span class="required"></span>

                                                 {{-- ✅ LATEST / HISTORY BADGE --}}
                                                 @if ($file->id === $latestManuscript->id)
                                                     <span class="badge bg-success ms-1" style="font-size:9px;">
                                                         Latest
                                                     </span>
                                                 @else
                                                     <span class="badge bg-secondary ms-1" style="font-size:9px;">
                                                         History
                                                     </span>
                                                 @endif
                                             </h4>
                                         </div>

                                         {{-- BODY --}}
                                         <div class="card-body">
                                             <div class="d-flex align-items-center">

                                                 {{-- STATUS ICON --}}
                                                 <span
                                                     class="avatar avatar-lg bg-success rounded-circle d-flex align-items-center justify-content-center">
                                                     <i class="ri-check-line fs-20 text-white"></i>
                                                 </span>

                                                 {{-- CONTENT --}}
                                                 <div class="d-flex flex-column border-start ms-2 px-2">
                                                     <p class="fw-semibold fs-12 mb-0 ms-1">
                                                         <i>Uploaded On: {{ $file->created_at->format('d-m-Y') }}</i>
                                                     </p>

                                                     <div class="d-flex gap-1 mt-1">
                                                         {{-- VIEW --}}
                                                         <span
                                                             class="fs-12 text-dark text-decoration-underline actionHandler px-1 cursor-pointer"
                                                             data-url="{{ route('file.viewframe') }}"
                                                             data-frame="offcanvas" data-handler="openFilePreview"
                                                             data-file-url="{{ getFileUrl($file->file_url) }}">
                                                             View
                                                         </span>
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>

                                     </div>
                                 @endforeach

                             </div>
                         </div>
                     </div>
                 </div>


                 {{-- <div class="text-end">
                     @if ($status == 'revise' || $status == 'editor-revise')
                         <span class="actionHandler btn btn-group btn-success"
                             data-url="{{ route('author.application.reSubmission', $application->uuid) }}">Re-submit
                             Manuscript</span>
                     @endif
                 </div> --}}
             </div>
         </div>
     </div>
 @endsection
 @section('javascripts')

     {{-- Submit confirmation --}}
     <script>
         window.confirmSubmit = (button) => {
             //  console.log('HRLLLL');
             //  return;

             const url = new URL(button.dataset.url);

             swal({
                 title: "Are you sure?",
                 text: 'If you like you change any detail go back otherwise continue with your submission',
                 icon: "warning",
                 buttons: true,
                 dangerMode: true,
             }).then((willDelete) => {
                 if (willDelete) {

                     const originalHTML = button.innerHTML;
                     button.disabled = true;
                     button.innerHTML = settings.spinner;
                     //  Calling the API
                     fetch(url, {
                             method: "GET",
                             headers: {
                                 ...settings.headers,
                                 Accept: "text/html, application/json",
                             },
                         })
                         .then((response) => response.json())
                         .then((data) => {
                             if (data.status == '200') {
                                 toastr.success(data.msg);
                                 if (data.redirect) {
                                     window.location.href = data.redirect;
                                     return;
                                 }
                                 return;
                             }
                             toastr.error('Unable to submit the application');
                         })
                         .catch((error) => {
                             toastr.error(error);
                         }).finally(() => {
                             button.disabled = false;
                             button.innerHTML = originalHTML;
                         });

                 } else {
                     return;
                 }

             });
         }
     </script>
     <script src="{{ asset('engines/fileviewer.js') }}"></script>

     {{-- back navigation prevention --}}
     <script>
         // Disable browser back button
         history.pushState(null, null, location.href);
         window.onpopstate = function() {
             history.go(1);
         };
     </script>
 @endsection
