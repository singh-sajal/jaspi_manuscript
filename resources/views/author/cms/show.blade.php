 @php
     $status = $application->status;
     $attachment_categories = [
         'Cover Letter',
         'First Page',
         //  'Upload Manuscript',
         'Upload ICMJE Declaration',
         'Supplimentry File',
         'Manuscript Image',
     ];

     $attachment_manuscripts = [
         'Upload Manuscript',
         'Upload Manuscript',
         'Upload Manuscript',
         'Upload Manuscript',
         'Upload Manuscript',
     ];

     //  $attachments = $application->attachments;

     $reviseCount = $application->timelines->where('status', 'revise')->count();

 @endphp
 @extends('author.app')
 @section('title', 'Manu Script Details')
 @section('breadcrumb', 'Manu Script Details')
 @section('page-title')
     Manu Script Details:
 @endsection
 @section('breadcrumb-button')

     @if ($status == 'revise')
         <span class="actionHandler btn btn-group btn-warning"
             data-url="{{ route('author.application.reSubmission', $application->uuid) }}">Re-submit Application</span>
     @endif

     @if ($status == 'incomplete' || $application->status == 'revise')
         <a class="btn btn-sm btn-primary btn-label" href="{{ route('author.application.edit', $application->uuid) }}">
             <i class="ri-edit-box-line" style="font-size: large"></i>
         </a>
     @else
         <button class="btn btn-sm btn-light btn-label" data-url="{{ route('author.application.edit', $application->uuid) }}">
             <i class="ri-edit-box-line"
                 style="border: 1px #d3c1c1 solid; border-radius: 4px;
                                background-color: #d3d3d3; color: #a9a9a9; cursor: not-allowed;"></i>
         </button>
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
     </style>

 @endsection
 @section('content')
     <div class="offcanvas offcanvas-end" tabindex="-1" style="z-index: 10000;width:80%" id="FileOffcanvas"
         data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="FileOffcanvasLabel"></div>
     <div class="modal" id="AjaxModal" tabindex="-1"></div>
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
                                         {{-- @foreach ($application->timelines as $timeline)
                                             @if ($timeline->status == 'reviewed')
                                                 <li class="fade-in">

                                                     <div class="timeline-badge {{ $color[$loop->index % 7] }}"></div>

                                                     <a class="timeline-panel text-muted">
                                                         <span>{{ $timeline->created_at->diffForHumans() }}</span>
                                                         <h6 class="mb-0">Status: <strong
                                                                 class="text-primary">{{ ucfirst($timeline->status) }}</strong>
                                                         </h6>
                                                         <p class="mb-0">Remark: {{ $timeline->remark }} <span
                                                                 class="text-primary actionHandler"
                                                                 data-url="{{ route('author.application.readMistake', $application->uuid) }}"
                                                                 style="cursor: pointer "><strong>read more</strong></span>
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
                                                             @if ($timeline->status == 'editor-revise' || $timeline->status == 'editor-reject')
                                                                 <span class="text-primary actionHandler"
                                                                     data-url="{{ route('author.application.readMistake', $application->uuid) }}"
                                                                     style="cursor: pointer "><strong>read
                                                                         more</strong></span>
                                                             @endif
                                                             @if ($timeline->status == 'reviewer-accepts' || $timeline->status == 'reviewer-revise' || $timeline->status == 'reviewer-reject')
                                                                 <span class="text-primary actionHandler"
                                                                     data-url="{{ route('author.application.reviewerReadMore', $application->uuid) }}"
                                                                     style="cursor: pointer "><strong>
                                                                         read more</strong></span>
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
                                                                 <strong>{{ $timeline->admin->name }}</strong>
                                                             </p>
                                                         @endif
                                                     </a>
                                                 </li>
                                             @endif
                                         @endforeach --}}
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
                                                                     data-url="{{ route('author.application.readMistake', $application->uuid) }}"
                                                                     style="cursor: pointer "><strong>read
                                                                         more</strong></span>
                                                             @endif
                                                             @if (
                                                                 $timeline->status == 'reviewer-accepts' ||
                                                                     $timeline->status == 'reviewer-revise' ||
                                                                     $timeline->status == 'reviewer-reject')
                                                                 <span class="text-primary actionHandler"
                                                                     data-url="{{ route('author.application.reviewerReadMore', $application->uuid) }}"
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
                                                                 data-url="{{ route('author.application.authorUpdate', $application->uuid) }}"
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
                             @if ($status == 'incomplete' || $application->status == 'revise')
                                 <button class="btn btn-sm btn-secondary actionHandler"
                                     data-url="{{ route('author.application.co_author', $application->uuid) }}">
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

                                         <th class="fs-14">Name/Email/Phone</th>
                                         <th class="fs-14">1-Author Affiliation,2-ORCID,3-SASPI</th>
                                         <th class="fs-14">Actions</th>

                                     </thead>
                                     <tbody>
                                         @foreach ($application->co_author_data ?? [] as $coAuthor)
                                             <tr id="co-author-{{ $coAuthor['coauthor_id'] }}">
                                                 <td>{{ $coAuthor['coauthor_name'] }}</br>
                                                     {{ $coAuthor['coauthor_email'] }}</br>
                                                     {{ $coAuthor['coauthor_phone'] }}</td>
                                                 <td>1-{{ $coAuthor['coauthor_affiliation'] ?? 'N/A' }}</br>
                                                     2-{{ $coAuthor['coauthor_orcid_id'] ?? 'N/A' }}</br>
                                                     3-{{ $coAuthor['coauthor_saspi_id'] ?? 'N/A' }}</td>
                                                 <td class="text-center">
                                                     @if ($status == 'incomplete' || $application->status == 'revise')
                                                         <div class="brg-group btn-group-sm">

                                                             <a class="text-info border-end actionHandler px-1"
                                                                 data-url="{{ route('author.application.co_author_update', [
                                                                     'uuid' => $application->uuid,
                                                                     'author_id' => $coAuthor['coauthor_id'],
                                                                 ]) }}">
                                                                 <i class="ri-edit-box-line" style="font-size: large"></i>
                                                             </a>
                                                             <a class="fs-12 text-danger btn-danger actionHandler px-1"
                                                                 data-action="delete"
                                                                 data-url="{{ route('author.application.co_author_delete', [
                                                                     'uuid' => $application->uuid,
                                                                     'author_id' => $coAuthor['coauthor_id'],
                                                                 ]) }}">
                                                                 <i class="ri-delete-bin-line" style="font-size: large"></i>
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
                                             <h4 class="card-title flex-grow-1 ms-1">
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
                                                             @if ($status == 'incomplete' || $application->status == 'revise')
                                                                 <a href="#"
                                                                     class="actionHandler fs-12 text-info text-decoration-underline border-start px-1"
                                                                     data-url="{{ route('author.application.file_upload', [
                                                                         'id' => $attachment == null ? 'na' : $attachment->id,
                                                                         'type' => $category,
                                                                         'app_id' => $application->id,
                                                                     ]) }}">Replace</a>
                                                             @endif
                                                             @if ($status == 'incomplete')
                                                                 <a href="#"
                                                                     class="fs-12 text-danger text-underlined actionHandler border-start px-1"
                                                                     data-action="delete"
                                                                     data-url="{{ route('author.application.file_destroy', ['app_id' => $application->uuid, 'id' => $attachment->id ?? 'na']) }}">
                                                                     <i class="ri-delete-bin-line"></i>
                                                                 </a>
                                                             @endif
                                                         </div>
                                                     </div>
                                                 @else
                                                     <div class="d-flex flex-column border-start ms-2 px-2">
                                                         <p class="fw-semibold fs-12 mb-0">No File Uploaded yet</p>
                                                         @if ($status == 'incomplete' || $application->status == 'revise')
                                                             <a href="#"
                                                                 class="actionHandler fs-12 text-info text-decoration-underline"
                                                                 data-url="{{ route('author.application.file_upload', [
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
                             <div class="d-flex align-items-center gap-2">
                                 @foreach ($attachment_manuscripts as $key => $category)
                                     @if ($loop->iteration > $reviseCount + 1)
                                         @break
                                     @endif

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
                                         @if ($loop->iteration < 2 || $application->status == 'revise')
                                             <div class="card flex-grow-1" style="max-width: 19%">
                                                 <div class="card-header d-flex align-items-center">
                                                     <div class="d-flex align-items-center justify-content-center bg-dark rounded-circle"
                                                         style="  height: 1.3rem;width: 1.3rem;font-size: 10px;">
                                                         <span class="text-white">{{ $loop->iteration }} </span>
                                                     </div>
                                                     <h4 class="card-title flex-grow-1 ms-1">
                                                         {{ $category }} <span
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
                                                             @if ($status == 'incomplete' || $application->status == 'revise')
                                                                 <a href="#"
                                                                     class="actionHandler fs-12 text-info text-decoration-underline"
                                                                     data-url="{{ route('author.application.file_upload', [
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
                                     <div class="card flex-grow-1" style="max-width: 19%">
                                         <div class="card-header d-flex align-items-center">
                                             <div class="d-flex align-items-center justify-content-center bg-dark rounded-circle"
                                                 style="  height: 1.3rem;width: 1.3rem;font-size: 10px;">
                                                 <span class="text-white">{{ $loop->iteration }} </span>
                                             </div>
                                             <h4 class="card-title flex-grow-1 ms-1">
                                                 {{ $category }} <span
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
                                                             @if ($status == 'incomplete')
                                                                 <a href="#"
                                                                     class="actionHandler fs-12 text-info text-decoration-underline border-start px-1"
                                                                     data-url="{{ route('author.application.file_upload', [
                                                                         'id' => $attachment == null ? 'na' : $attachment->id,
                                                                         'type' => $category,
                                                                         'app_id' => $application->id,
                                                                     ]) }}">Replace</a>
                                                             @endif
                                                             @if ($status == 'incomplete')
                                                                 <a href="#"
                                                                     class="fs-12 text-danger text-underlined actionHandler border-start px-1"
                                                                     data-action="delete"
                                                                     data-url="{{ route('author.application.file_destroy', ['app_id' => $application->uuid, 'id' => $attachment->id ?? 'na']) }}">
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

     {{-- Revise confirmation --}}
     {{-- <script>
         window.confirmRevise = (button) => {

             const url = new URL(button.dataset.url);

             swal({
                 title: "Are you sure?",
                 text: 'If you like you change any detail go back otherwise continue with your resubmission',
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
                             toastr.error('Unable to revised submit the application');
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
     <script src="{{ asset('engines/fileviewer.js') }}"></script>
 @endsection
