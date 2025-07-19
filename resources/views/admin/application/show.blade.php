 @php
     $status = $application->status;
     if (!isReviewer()) {
         $attachment_categories = [
             'Cover Letter',
             'First Page',
             //  'Upload Manuscript',
             'Upload ICMJE Declaration',
             'Supplimentry File',
             'Manuscript Image',
         ];
     } else {
         $attachment_categories = [
             'Cover Letter',
             //  'First Page',
             //  'Upload Manuscript',
             'Upload ICMJE Declaration',
             'Supplimentry File',
             'Manuscript Image',
         ];
     }

     $attachment_manuscripts = [
         'Upload Manuscript',
         'Upload Manuscript',
         'Upload Manuscript',
         'Upload Manuscript',
         'Upload Manuscript',
     ];

     //  $attachments = $application->attachments;

 @endphp
 @extends('admin.app')
 @section('title', 'Manu Script Submission Details')
 @section('breadcrumb', 'Manu Script Submission Details')
 @section('page-title')
     Manu Script Submission Details:
 @endsection
 @section('breadcrumb-button')


     {{-- @if (isEditor() && $application->status === 'processing')
         <span class="btn btn-success btn-sm actionHandler"
             data-url="{{ route('admin.application.assignReviewer', $application->uuid) }}">
             Assign Reviewer
         </span>
     @endif --}}

     {{-- @if (isEditor() && $application->status === 'reviewed')
         <span>
             <button class="btn btn-group-sm btn-sm btn-success actionHandler"
                 data-url="{{ route('admin.application.acceptance', $application->uuid) }}"
                 data-handler="acceptApplication">Accept</button> |
             <button class="btn btn-group-sm btn-sm btn-warning actionHandler"
                 data-url="{{ route('admin.application.revise', $application->uuid) }}"
                 data-handler="reviseApplication">Request Revision</button> |
             <button class="btn btn-group-sm btn-sm btn-danger actionHandler"
                 data-url="{{ route('admin.application.rejection', $application->uuid) }}"
                 data-handler="confirmReject">Reject</button>
         </span>
     @endif

     @if (isEditor() && $application->status === 'accepted')
         <span class="btn btn-success btn-sm actionHandler"
             data-url="{{ route('admin.application.assignPublisher', $application->uuid) }}">
             Assign Publisher
         </span>
     @endif --}}

     {{-- @if (isReviewer() && $application->status === 'under review')
         <span class="btn btn-success btn-sm actionHandler"
             data-url="{{ route('admin.application.reviewed', $application->uuid) }}">
             Mark as reviewed
         </span>
     @endif --}}

     {{-- @if (isReviewer() && $application->status === 'reviewed')
         <span class="btn btn-success btn-sm actionHandler"
             data-url="{{ route('admin.application.assignPublisher', $application->uuid) }}">
             Assign Publisher
         </span>
     @endif --}}

     @if ((isSuperAdmin() || isReviewer()) && $application->status === 'under review')
     <span class="btn btn-success btn-sm actionHandler"
         data-url="{{ route('admin.application.reviewScore', $application->uuid) }}">
         Score Application
     </span>
     @endif

     @if ((isSuperAdmin() || isPublisher()) && $application->status == 'prepublished')
         <button class="btn btn-success btn-sm actionHandler"
             data-url="{{ route('admin.application.publish', $application->uuid) }}">
             Publish
         </button>
     @endif

     @if ((isSuperAdmin() || isEditor()) && $application->status === 'processing')
         <button class="btn btn-success btn-sm actionHandler"
             data-url="{{ route('admin.application.editorDecision', $application->uuid) }}">
             Make a decision
         </button>
     @endif

     @if (isSuperAdmin() && ($application->status === 'editor-revise' || $application->status === 'reviewer-revise'))
         <button class="btn btn-group-sm btn-sm btn-warning actionHandler"
             data-url="{{ route('admin.application.revise', $application->uuid) }}" data-handler="reviseApplication">
             Request Revision</button>
     @endif

     @if (isSuperAdmin() && ($application->status === 'editor-reject' || $application->status === 'reviewer-rejects'))
         <button class="btn btn-group-sm btn-sm btn-danger actionHandler"
             data-url="{{ route('admin.application.rejection', $application->uuid) }}"
             data-handler="confirmReject">Reject</button>
     @endif

     @if (isSuperAdmin() && $application->status === 'reviewer-accepts')
         <span class="btn btn-success btn-sm actionHandler"
             data-url="{{ route('admin.application.assignPublisher', $application->uuid) }}">
             Assign Publisher
         </span>
     @endif






     <div class="d-flex align-items-center justify-content-end">

         @if (isSuperAdmin() && ($application->status == 'submitted' || $application->status == 'processing'))
             <a class="btn btn-sm btn-dark btn-label" href="{{ route('admin.application.edit', $application->uuid) }}">
                 <i class="ri-edit-box-line" style=""></i>
             </a>
         @endif

         <a class="btn btn-sm btn-primary" href="{{ route('admin.application.index') }}">
             <i class="ri-arrow-go-back-line"></i>
         </a>
     </div>
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
     </style>

 @endsection
 @section('content')
     @php
         $latestTimeline = $application->timelines()->orderBy('created_at', 'desc')->first();
     @endphp

     <div class="offcanvas offcanvas-end" tabindex="-1" style="z-index: 10000;width:80%" id="FileOffcanvas"
         data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="FileOffcanvasLabel"></div>

     <div class="modal" id="AjaxModal" tabindex="-1"></div>

     {{-- @if (isEditor() && $application->status === 'reviewed')
         <div class="container my-3">
             <span><strong>Reviewer's comment: </strong>{{$latestTimeline->data }}</span>
         </div>
     @endif --}}

     <div class="card">
         <div class="card-body">
             <div class="row">
                 <div class="col-7">
                     <div class="card">
                         <div class="card-header d-flex align-items-center justify-content-between">
                             <h4 class="card-title">#{{ $application->application_id }}</h4>
                             <span class="badge badge-sm bg-dark">{{ ucfirst($application->status) }}</span>
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
                                                 <td class="fw-semibold">SASPI ID</td>
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
                                                             'Plagiarism Check',
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
                                         @endphp
                                         @foreach ($application->timelines as $timeline)
                                             @if (
                                                 $timeline->status == 'editor-revise' ||
                                                     $timeline->status == 'editor-reject' ||
                                                     $timeline->status == 'reviewer-accepts' ||
                                                     $timeline->status == 'reviewer-revise' ||
                                                     $timeline->status == 'reviewer-rejects')
                                                 <li class="fade-in">

                                                     <div class="timeline-badge {{ $color[$loop->index % 7] }}"></div>

                                                     <a class="timeline-panel text-muted">
                                                         <span>{{ $timeline->created_at->diffForHumans() }}</span>
                                                         <h6 class="mb-0">Status: <strong
                                                                 class="text-primary">{{ ucfirst($timeline->status) }}</strong>
                                                         </h6>
                                                         <p class="mb-0">Remark: {{ $timeline->remark }}
                                                             @if ($timeline->status == 'editor-revise' || $timeline->status == 'editor-reject')
                                                                 <span class="text-primary actionHandler"
                                                                     data-url="{{ route('admin.application.readMistake', $application->uuid) }}"
                                                                     style="cursor: pointer "><strong>read
                                                                         more</strong></span>
                                                             @endif
                                                             @if (
                                                                 $timeline->status == 'reviewer-accepts' ||
                                                                     $timeline->status == 'reviewer-revise' ||
                                                                     $timeline->status == 'reviewer-reject')
                                                                 <span class="text-primary actionHandler"
                                                                     data-url="{{ route('admin.application.reviewerReadMore', $application->uuid) }}"
                                                                     style="cursor: pointer "><strong>
                                                                         read more</strong></span>
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
                                                         <p class="mb-0">Remark: {{ $timeline->remark }} <span
                                                                 class="text-primary actionHandler"
                                                                 data-url="{{ route('admin.application.authorUpdate', $application->uuid) }}"
                                                                 style="cursor: pointer "><strong>read more</strong></span>
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
                                                                 <strong>{{ $timeline->admin->name }}</strong>
                                                             </p>
                                                         @endif
                                                     </a>
                                                 </li>
                                             @endif
                                         @endforeach
                                     </ul>
                                 </div>
                             </div>
                         </div>
                     </div>
                 @endif
                 <div class="{{ $application->status == 'incomplete' ? 'col-5' : 'col-12' }}">
                     <div class="card">
                         <div class="card-header d-flex align-items-center justify-content-between">
                             <h4 class="card-title">Co-Authors</h4>
                             @if (isSuperAdmin() && ($application->status == 'submitted' || $application->status == 'processing'))
                                 <button class="btn btn-sm btn-secondary actionHandler"
                                     data-url="{{ route('admin.application.co_author', $application->uuid) }}">
                                     <i class="ri-add-circle-line" style="font-size: large"></i>
                                 </button>
                             @endif
                         </div>

                         <div class="card-body p-0" id="co-author-container">
                             @if (empty($application->co_author_data))
                                 <p class="fw-semibold p-3 text-center">No Data Available</p>
                             @else
                                 <table class="table-bordered table">
                                     <thead>

                                         <th class="fs-14">Name</th>
                                         <th class="fs-14">Email</th>
                                         <th class="fs-14">Phone</th>
                                         <th class="fs-14">Affiliation</th>
                                         <th class="fs-14">Orcid_id</th>
                                         <th class="fs-14">Saspi_id</th>
                                         <th class="fs-14">Actions</th>

                                     </thead>
                                     <tbody>
                                         @foreach ($application->co_author_data ?? [] as $coAuthor)
                                             <tr id="co-author-{{ $coAuthor['coauthor_id'] }}">
                                                 <td>{{ $coAuthor['coauthor_name'] }}</td>
                                                 <td>{{ $coAuthor['coauthor_email'] }}</td>
                                                 <td>{{ $coAuthor['coauthor_phone'] }}</td>
                                                 <td>{{ $coAuthor['coauthor_affiliation'] ?? 'N/A' }}</td>
                                                 <td>{{ $coAuthor['coauthor_orcid_id'] ?? 'N/A' }}</td>
                                                 <td>{{ $coAuthor['coauthor_saspi_id'] ?? 'N/A' }}</td>
                                                 <td class="text-center">
                                                     @if (isSuperAdmin() && ($application->status == 'submitted' || $application->status == 'processing'))
                                                         <div class="brg-group btn-group-sm">

                                                             <a class="text-info border-end actionHandler px-1"
                                                                 data-url="{{ route('admin.application.co_author_update', [
                                                                     'uuid' => $application->uuid,
                                                                     'author_id' => $coAuthor['coauthor_id'],
                                                                 ]) }}">
                                                                 <i class="ri-edit-box-line" style="font-size: large"></i>
                                                             </a>
                                                             <a class="fs-12 text-danger btn-danger actionHandler px-1"
                                                                 data-action="delete"
                                                                 data-url="{{ route('admin.application.co_author_delete', [
                                                                     'uuid' => $application->uuid,
                                                                     'author_id' => $coAuthor['coauthor_id'],
                                                                 ]) }}">
                                                                 <i class="ri-delete-bin-line"
                                                                     style="font-size: large"></i>
                                                             </a>
                                                         </div>
                                                     @endif
                                                 </td>
                                             </tr>
                                         @endforeach
                                     </tbody>
                                 </table>



                             @endif
                         </div>
                     </div>
                 </div>
                 <div class="col-12">
                     <div class="card-body">

                         <p class="fw-semibold">Description</p>
                         <div>

                             {!! $application->description ?? '' !!}
                         </div>
                     </div>
                 </div>
                 <div class="col-12">
                     <div class="card mt-2">
                         <div class="card-header">
                             <h4 class="card-title">Attachments</h4>
                         </div>
                         <div class="card-body">
                             <div class="d-flex align-items-center justify-content-between gap-2">
                                 @foreach ($attachment_categories as $key => $category)
                                     @php

                                         if (!empty($otherfiles)) {
                                             $attachment = $otherfiles->where('attachment_type', $category)->first();
                                         } else {
                                             $attachment = null;
                                         }
                                     @endphp
                                     <div class="card flex-grow-1">
                                         <div class="card-header d-flex align-items-center">
                                             <div class="d-flex align-items-center justify-content-center bg-dark rounded-circle"
                                                 style="  height: 1.3rem;width: 1.3rem;font-size: 10px;">
                                                 <span class="text-white">{{ $loop->iteration }} </span>
                                             </div>
                                             <h4 class="card-title flex-grow-1 ms-1" style="font-size:12px;">
                                                 {{ str_replace('Upload', '', $category) }}
                                                 <span class="{{ $loop->iteration != 5 ? 'required' : '' }}"></span>
                                             </h4>

                                         </div>

                                         <div class="card-body">
                                             <input type="file" id="fileInput" style="display: none" />
                                             <div class="d-flex align-items-center">
                                                 <span
                                                     class="avatar avatar-lg {{ $attachment == null ? 'bg-danger' : 'bg-success' }} rounded-circle d-flex align-items-center justify-content-center">
                                                     <i id="fileStatusIcon"
                                                         class="{{ $attachment == null ? 'ri-file-2-line' : 'ri-check-line' }} fs-20 text-white"
                                                         title="No file uploaded"></i>
                                                 </span>
                                                 @if ($attachment != null)
                                                     <div class="d-flex flex-column border-start ms-2 px-2">
                                                         <p class="fw-semibold fs-12 mb-0 ms-1"><i>Uploaded On:
                                                                 {{ $attachment->created_at->format('d-m-Y') }}</i></p>
                                                         <div class="d-flex gap-1">

                                                             <span
                                                                 class="fs-12 text-dark text-decoration-underline actionHandler px-1"
                                                                 data-url="{{ route('file.viewframe') }}"
                                                                 data-frame="offcanvas"
                                                                 data-file-url="{{ getFileUrl($attachment->file_url ?? '') }}"
                                                                 data-handler="openFilePreview" target="_blank"
                                                                 title="View Uploaded File">
                                                                 View
                                                             </span>
                                                             @if (isSuperAdmin() && $application->status == 'submitted')
                                                                 <a href="#"
                                                                     class="actionHandler fs-12 text-info text-decoration-underline border-start px-1"
                                                                     data-url="{{ route('admin.application.file_upload', [
                                                                         'id' => $attachment == null ? 'na' : $attachment->id,
                                                                         'type' => $category,
                                                                         'app_id' => $application->id,
                                                                     ]) }}">Replace</a>
                                                                 <a href="#"
                                                                     class="fs-12 text-danger text-underlined actionHandler border-start px-1"
                                                                     data-action="delete"
                                                                     data-url="{{ route('admin.application.file_destroy', ['app_id' => $application->uuid, 'id' => $attachment->id ?? 'na']) }}">
                                                                     <i class="ri-delete-bin-line"></i>
                                                                 </a>
                                                             @endif


                                                         </div>
                                                     </div>
                                                 @else
                                                     <div class="d-flex flex-column border-start ms-2 px-2">
                                                         <p class="fw-semibold fs-12 mb-0">No File Uploaded yet</p>

                                                         @if (isSuperAdmin() && $application->status == 'submitted')
                                                             <a href="#"
                                                                 class="actionHandler fs-12 text-info text-decoration-underline"
                                                                 data-url="{{ route('admin.application.file_upload', [
                                                                     'id' => $attachment == null ? 'na' : $attachment->id,
                                                                     'type' => $category,
                                                                     'app_id' => $application->id,
                                                                 ]) }}">Upload</a>
                                                         @endif
                                                     </div>
                                                 @endif
                                             </div>

                                         </div>


                                     </div>
                                 @endforeach
                             </div>
                         </div>
                         <div class="card-body">
                             <div class="d-flex align-items-center justify-content-between gap-2">
                                 @foreach ($attachment_manuscripts as $key => $category)
                                     @php
                                         if (!empty($manuScriptFiles)) {
                                             $attachment = $manuScriptFiles
                                                 ->where('attachment_type', $category)
                                                 ->first();
                                             $manuScriptFiles = $manuScriptFiles->reject(function ($item) use (
                                                 $attachment,
                                             ) {
                                                 return $item->id === $attachment->id;
                                             });
                                         } else {
                                             $attachment = null;
                                         }
                                     @endphp
                                     @if ($attachment === null)
                                         @if ($loop->iteration < 2 || $application->status == 'revised_submission')
                                             <div class="card flex-grow-1">
                                                 <div class="card-header d-flex align-items-center">
                                                     <div class="d-flex align-items-center justify-content-center bg-dark rounded-circle"
                                                         style="  height: 1.3rem;width: 1.3rem;font-size: 10px;">
                                                         <span class="text-white">{{ $loop->iteration }} </span>
                                                     </div>
                                                     <h4 class="card-title flex-grow-1 ms-1" style="font-size:12px;">
                                                         {{ str_replace('Upload', '', $category) }}
                                                         <span
                                                             class="{{ $loop->iteration != 5 ? 'required' : '' }}"></span>
                                                     </h4>

                                                 </div>

                                                 <div class="card-body">
                                                     <input type="file" id="fileInput" style="display: none" />
                                                     <div class="d-flex align-items-center">
                                                         <span
                                                             class="avatar avatar-lg {{ $attachment == null ? 'bg-danger' : 'bg-success' }} rounded-circle d-flex align-items-center justify-content-center">
                                                             <i id="fileStatusIcon"
                                                                 class="{{ $attachment == null ? 'ri-file-2-line' : 'ri-check-line' }} fs-20 text-white"
                                                                 title="No file uploaded"></i>
                                                         </span>

                                                         <div class="d-flex flex-column border-start ms-2 px-2">
                                                             <p class="fw-semibold fs-12 mb-0">No File Uploaded yet</p>

                                                             @if (isSuperAdmin() && ($status == 'incomplete' || $application->status == 'revised'))
                                                                 <a href="#"
                                                                     class="actionHandler fs-12 text-info text-decoration-underline"
                                                                     data-url="{{ route('admin.application.file_upload', [
                                                                         'id' => $attachment == null ? 'na' : $attachment->id,
                                                                         'type' => $category,
                                                                         'app_id' => $application->id,
                                                                     ]) }}">Upload</a>
                                                             @endif
                                                         </div>

                                                     </div>

                                                 </div>


                                             </div>
                                         @endif
                                         @break
                                     @endif

                                     {{-- if attachment not null --}}
                                     <div class="card flex-grow-1">
                                         <div class="card-header d-flex align-items-center">
                                             <div class="d-flex align-items-center justify-content-center bg-dark rounded-circle"
                                                 style="  height: 1.3rem;width: 1.3rem;font-size: 10px;">
                                                 <span class="text-white">{{ $loop->iteration }} </span>
                                             </div>
                                             <h4 class="card-title flex-grow-1 ms-1" style="font-size:12px;">
                                                 {{ str_replace('Upload', '', $category) }}
                                                 <span class="{{ $loop->iteration != 5 ? 'required' : '' }}"></span>
                                             </h4>

                                         </div>

                                         <div class="card-body">
                                             <input type="file" id="fileInput" style="display: none" />
                                             <div class="d-flex align-items-center">
                                                 <span
                                                     class="avatar avatar-lg {{ $attachment == null ? 'bg-danger' : 'bg-success' }} rounded-circle d-flex align-items-center justify-content-center">
                                                     <i id="fileStatusIcon"
                                                         class="{{ $attachment == null ? 'ri-file-2-line' : 'ri-check-line' }} fs-20 text-white"
                                                         title="No file uploaded"></i>
                                                 </span>
                                                 @if ($attachment != null)
                                                     <div class="d-flex flex-column border-start ms-2 px-2">
                                                         <p class="fw-semibold fs-12 mb-0 ms-1"><i>Uploaded On:
                                                                 {{ $attachment->created_at->format('d-m-Y') }}</i></p>
                                                         <div class="d-flex gap-1">

                                                             <span
                                                                 class="fs-12 text-dark text-decoration-underline actionHandler px-1"
                                                                 data-url="{{ route('file.viewframe') }}"
                                                                 data-frame="offcanvas"
                                                                 data-file-url="{{ getFileUrl($attachment->file_url ?? '') }}"
                                                                 data-handler="openFilePreview" target="_blank"
                                                                 title="View Uploaded File">
                                                                 View
                                                             </span>
                                                             @if (isSuperAdmin() && $application->status == 'submitted')
                                                                 <a href="#"
                                                                     class="actionHandler fs-12 text-info text-decoration-underline border-start px-1"
                                                                     data-url="{{ route('admin.application.file_upload', [
                                                                         'id' => $attachment == null ? 'na' : $attachment->id,
                                                                         'type' => $category,
                                                                         'app_id' => $application->id,
                                                                     ]) }}">Replace</a>
                                                                 <a href="#"
                                                                     class="fs-12 text-danger text-underlined actionHandler border-start px-1"
                                                                     data-action="delete"
                                                                     data-url="{{ route('admin.application.file_destroy', ['app_id' => $application->uuid, 'id' => $attachment->id ?? 'na']) }}">
                                                                     <i class="ri-delete-bin-line"></i>
                                                                 </a>
                                                             @endif


                                                         </div>
                                                     </div>
                                                 @endif
                                             </div>

                                         </div>


                                     </div>
                                 @endforeach
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 @endsection
 @section('javascripts')
     {{-- application Accept  --}}
     <script>
         window.acceptApplication = (button) => {

             const url = new URL(button.dataset.url);

             swal({
                 title: "Are you sure?",
                 text: 'This action can not be reversed!',
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
                             toastr.error('Unable to accept application');
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

     {{-- Revise Application --}}
     <script>
         window.reviseApplication = (button) => {

             const url = new URL(button.dataset.url);

             swal({
                 title: "Are you sure?",
                 text: 'This action can not be reversed!',
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
                             toastr.error('Unable to revise application');
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



     {{-- Publish Application --}}
     {{-- <script>
         window.confirmPublish = (button) => {

             const url = new URL(button.dataset.url);

             swal({
                 title: "Are you sure?",
                 text: 'This action can not be reversed!',
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
                             toastr.error('Unable to Publish application');
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
     </script> --}}

     {{-- Reject Application --}}
     <script>
         window.confirmReject = (button) => {

             const url = new URL(button.dataset.url);

             swal({
                 title: "Are you sure?",
                 text: 'This action can not be reversed!',
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
                             toastr.error('Unable to revise application');
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

     {{-- Some basic functions like calculate total score etc --}}
     <script>
         document.addEventListener('DOMContentLoaded', function() {
             const modal = document.querySelector('.modal');
             if (modal) {
                 modal.addEventListener('shown.bs.modal', initializeFormListeners);
             } else {
                 initializeFormListeners(); // Fallback if modal is already loaded
             }
         });

         function displayoption() {
             document.getElementById('question-7').style.display = 'block';
             const q20Radios = document.querySelectorAll('input[name="q20"]');
             q20Radios.forEach(radio => radio.setAttribute('required', 'required'));
         }

         function displayoption2() {
             document.getElementById('question-7').style.display = 'none';
             const q20Radios = document.querySelectorAll('input[name="q20"]');
             q20Radios.forEach(radio => {
                 radio.removeAttribute('required');
                 radio.checked = false;
             });
             document.getElementById('q20_hidden').value = '';
             updateQ8Selection();
         }


         document.addEventListener('DOMContentLoaded', function() {
             initializeQ14Logic(); // Call Q14 logic directly
         });

         function initializeQ14Logic() {
             const q14Radios = document.querySelectorAll('input[name="q14"]');
             const q14Textarea = document.getElementById('q14-textarea');
             const q14TextInput = document.getElementById('question-14');
             const q14Hidden = document.getElementById('q14_hidden');

             q14Radios.forEach(radio => {
                 radio.addEventListener('change', function() {
                     if (this.value === 'yes') {
                         q14Textarea.style.display = 'block';
                         q14Hidden.value = 'yes (' + (q14TextInput.value.trim() || '') + ')';
                     } else {
                         q14Textarea.style.display = 'none';
                         q14TextInput.value = '';
                         q14Hidden.value = 'no';
                     }
                 });
             });

             q14TextInput?.addEventListener('input', function() {
                 const selected = document.querySelector('input[name="q14"]:checked')?.value;
                 if (selected === 'yes') {
                     q14Hidden.value = 'yes (' + this.value.trim() + ')';
                 }
             });
         }


         function initializeFormListeners() {
             try {
                 function updateHiddenInput(questionNumber) {
                     const radios = document.querySelectorAll(`input[name="q${questionNumber}"]`);
                     const hiddenInput = document.getElementById(`q${questionNumber}_hidden`);
                     if (!hiddenInput) return;
                     radios.forEach(radio => {
                         radio.addEventListener('change', function() {
                             hiddenInput.value = this.value;
                             if (questionNumber >= 1 && questionNumber <= 6) {
                                 updateQ8Selection();
                             }
                         });
                     });
                 }

                 //  change
                 function updateHiddenInput(questionNumber) {
                     const radios = document.querySelectorAll(`input[name="q${questionNumber}"]`);
                     const hiddenInput = document.getElementById(`q${questionNumber}_hidden`);
                     if (!hiddenInput) return;
                     radios.forEach(radio => {
                         radio.addEventListener('change', function() {
                             hiddenInput.value = this.value;
                             if (questionNumber >= 1 && questionNumber <= 6) {
                                 updateQ8Selection();
                             } else if (questionNumber === 14) {
                                 const q14Textarea = document.getElementById('q14-textarea');
                                 if (this.value === 'yes') {
                                     q14Textarea.style.display = 'block';
                                     document.getElementById('question-14').setAttribute('required',
                                         'required');
                                 } else {
                                     q14Textarea.style.display = 'none';
                                     document.getElementById('question-14').removeAttribute('required');
                                     document.getElementById('question-14').value = '';
                                 }
                             }
                         });
                     });
                 }

                 function updateTextInput(questionNumber) {
                     const textarea = document.getElementById(`question-${questionNumber}`);
                     const hiddenInput = document.getElementById(`q${questionNumber}_hidden`);
                     if (!textarea || !hiddenInput) return;
                     textarea.addEventListener('input', function() {
                         hiddenInput.value = this.value;
                     });
                 }

                 function updateQ7HiddenInput() {
                     const q7Radios = document.querySelectorAll('input[name="q7"]');
                     const q7Hidden = document.getElementById('q7_hidden');
                     const q20Radios = document.querySelectorAll('input[name="q20"]');
                     const q20Hidden = document.getElementById('q20_hidden');

                     q7Radios.forEach(radio => {
                         radio.addEventListener('change', function() {
                             q7Hidden.value = this.value;
                             if (this.value === 'no') {
                                 q20Hidden.value = '';
                                 q20Radios.forEach(r => {
                                     r.removeAttribute('required');
                                     r.checked = false;
                                 });
                             } else {
                                 q20Radios.forEach(r => r.setAttribute('required', 'required'));
                             }
                             updateQ8Selection();
                         });
                     });

                     q20Radios.forEach(radio => {
                         radio.addEventListener('change', function() {
                             q20Hidden.value = this.value;
                             updateQ8Selection();
                         });
                     });
                 }

                 function updateQ8Selection() {
                     let sum = 0;
                     for (let i = 1; i <= 6; i++) {
                         const hiddenInput = document.getElementById(`q${i}_hidden`);
                         if (hiddenInput.value) {
                             sum += parseInt(hiddenInput.value, 10) || 0;
                         }
                     }
                     const q7Hidden = document.getElementById('q7_hidden');
                     const q20Hidden = document.getElementById('q20_hidden');
                     if (q7Hidden.value === 'yes' && q20Hidden.value) {
                         sum += parseInt(q20Hidden.value, 10) || 0;
                     }

                     const q8Radios = document.querySelectorAll('input[name="q8"]');
                     const q8Hidden = document.getElementById('q8_hidden');
                     const q17Radios = document.querySelectorAll('input[name="q17"]');
                     const q17Hidden = document.getElementById('q17_hidden');
                     const q18Radios = document.querySelectorAll('input[name="q18"]');
                     const q18Hidden = document.getElementById('q18_hidden');

                     let selectedValue = 'reject';
                     if (sum >= 28 && sum <= 35) {
                         selectedValue = 'accept';
                     } else if (sum >= 21 && sum <= 27) {
                         selectedValue = 'minor';
                     } else if (sum >= 14 && sum <= 20) {
                         selectedValue = 'major';
                     }

                     q8Radios.forEach(radio => {
                         radio.checked = radio.value === selectedValue;
                         radio.disabled = radio.value !== selectedValue;
                     });
                     q8Hidden.value = selectedValue;

                     q17Radios.forEach(radio => {
                         radio.checked = radio.value === selectedValue;
                         radio.disabled = radio.value !== selectedValue;
                     });
                     q17Hidden.value = selectedValue;

                     q18Radios.forEach(radio => {
                         radio.checked = radio.value === selectedValue;
                         radio.disabled = radio.value !== selectedValue;
                     });
                     q18Hidden.value = selectedValue;
                 }

                 // Initialize listeners for radio questions (Q1–Q14, Q17–Q18)
                 for (let i = 1; i <= 18; i++) {
                     if (i !== 15 && i !== 16 && i !== 19) {
                         updateHiddenInput(i);
                     }
                 }

                 // Initialize listeners for textarea questions (Q15, Q16, Q19)
                 updateTextInput(15);
                 updateTextInput(16);
                 updateTextInput(19);

                 updateQ7HiddenInput();

                 document.querySelector('form').addEventListener('submit', function(event) {
                     let isValid = true;
                     const errorElement = document.querySelector('[data-error="review_status"]');
                     errorElement.textContent = ''; // Clear previous errors

                     // Validate radio questions
                     for (let i = 1; i <= 18; i++) {
                         if (i !== 15 && i !== 16 && i !== 19) {
                             const radios = document.querySelectorAll(`input[name="q${i}"]:checked`);
                             const hiddenInput = document.getElementById(`q${i}_hidden`);
                             if (!radios.length && hiddenInput) {
                                 isValid = false;
                                 errorElement.textContent = `Please answer question ${i}.`;
                                 break;
                             } else if (radios.length && hiddenInput) {
                                 hiddenInput.value = radios[0].value;
                             }
                         }
                     }

                     // Validate Q20 only if Q7 is "Yes"
                     const q7Hidden = document.getElementById('q7_hidden');
                     const q20Radios = document.querySelectorAll('input[name="q20"]:checked');
                     const q20Hidden = document.getElementById('q20_hidden');
                     if (q7Hidden.value === 'yes' && !q20Radios.length) {
                         isValid = false;
                         errorElement.textContent = 'Please answer question 7 (Methodology Validity) sub-question.';
                     } else if (q20Radios.length && q20Hidden) {
                         q20Hidden.value = q20Radios[0].value;
                     }

                     // Validate textarea questions
                     for (let i of [15, 16, 19]) {
                         const textarea = document.getElementById(`question-${i}`);
                         const hiddenInput = document.getElementById(`q${i}_hidden`);
                         if (textarea && !textarea.value.trim()) {
                             isValid = false;
                             errorElement.textContent = `Please provide input for question ${i}.`;
                             break;
                         } else if (textarea && hiddenInput) {
                             hiddenInput.value = textarea.value.trim();
                         }
                     }

                     updateQ8Selection();
                     if (!isValid) {
                         event.preventDefault();
                     }
                 });
             } catch (error) {
                 console.error('Error initializing form listeners:', error);
             }
         }
     </script>
     <script>
         function displayoption() {
             document.getElementById('question-7').style.display = 'block';
         }

         function displayoption2() {
             document.getElementById('question-7').style.display = 'none';
             document.querySelectorAll('input[name="q20"]').forEach((input) => {
                 input.checked = false;
             });
         }

         function updateScore() {
             let totalScore = 0;
             const questions = ['q1', 'q2', 'q3', 'q4', 'q5', 'q6', 'q20'];
             questions.forEach((q) => {
                 const selected = document.querySelector(`input[name="${q}"]:checked`);
                 if (selected) {
                     totalScore += parseInt(selected.value) || 0;
                 }
             });
             document.getElementById('totalScore').textContent = totalScore;

             // Update recommendation radio buttons based on total score
             const q8Radios = document.querySelectorAll('input[name="q8"]');
             q8Radios.forEach(radio => radio.disabled = false);
             if (totalScore >= 28) {
                 document.getElementById('q8-accept').checked = true;
             } else if (totalScore >= 21) {
                 document.getElementById('q8-minor').checked = true;
             } else if (totalScore >= 14) {
                 document.getElementById('q8-major').checked = true;
             } else {
                 document.getElementById('q8-reject').checked = true;
             }
         }

         // Add event listeners to all radio buttons for questions 1 to 7
         document.querySelectorAll(
             'input[type="radio"][name="q1"], input[type="radio"][name="q2"], input[type="radio"][name="q3"], input[type="radio"][name="q4"], input[type="radio"][name="q5"], input[type="radio"][name="q6"], input[type="radio"][name="q20"]'
             ).forEach((input) => {
             input.addEventListener('change', updateScore);
         });

         // Initialize score on page load
         updateScore();
     </script>

     {{-- Scripts for Editor decision --}}








 @endsection
