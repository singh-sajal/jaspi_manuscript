@php
    $statusColorMap = [
        'submitted' => 'primary',
        'Accepted' => 'success',
        'Under Review' => 'warning',
        'Published' => 'success',
        'Rejected' => 'danger',
        'Revised' => 'warning',
    ];
@endphp
@forelse ($applications as $application)
    <tr>

        <td>
            <div class="d-flex align-items-center">


                <a class="d-flex flex-column" href="{{ route('admin.application.show', $application->uuid ?? '') }}">
                    <span class="text-dark fw-bold">{{ Str::limit($application->title, 20, '...') }}</span>
                    <span class="text-muted">{{ $application->application_id ?? '---' }}</span>
                </a>
            </div>
        </td>
        <td>
            @php
                if ($application->submission_type == 'new_submission') {
                    $record = 'New Submission';
                } else {
                    $record = 'Revised Submission';
                }

            @endphp
            <div class="d-flex align-items-center">
                <span>{{ $record ?? '---' }}</span>
                {{-- <i class="ri-checkbox-circle-fill text-info ms-1"></i> --}}
            </div>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <ul>
                    @foreach ($application->article_type ?? [] as $articalType)
                        <li>-{{ $articalType }}</li>
                    @endforeach
                </ul>
            </div>
        </td>
        @if (isSuperAdmin())
            <td>
                <div class="d-flex align-items-center">
                    <span>{{ $application->author->name ?? '---' }}</span>
                    <i class="ri-checkbox-circle-fill text-info ms-1"></i>
                </div>
            </td>
        @endif
        <td>
            <div class="d-flex align-items-center {{ isSuperAdmin() && ($application->status == 'submitted' || $application->status == 'resubmitted') ? 'actionHandler' : '' }}"
                data-url="{{ route('admin.application.assign', $application->uuid) }}">

                <img class="avatar avatar-sm me-2"
                    src="{{ asset($application->assignee->avatar ?? 'admin/assets/images/avatar/1.png') }}"
                    alt="avatar">

                {{-- Add the name and username into flex column the username a slight smaller --}}

                <span class="text-dark fw-bold">{{ $application->assignee->name ?? '---' }}</span>



            </div>
        </td>






        <td><span class="badge badge-sm badge-rounded badge-{{ $statusColorMap[$application->status] ?? 'primary' }}">
                {{ Str::ucfirst($application->status ?? '---') }}
            </span>
            @if ($application->status == 'published')
                <div class="ms-2 my-3">
                    <span class="fs-12 text-dark text-decoration-underline actionHandler px-1 text-center"
                        data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                        data-file-url="{{ getFileUrl($application->data['file_url'] ?? '') }}"
                        data-handler="openFilePreview" target="_blank" title="View Uploaded File">
                        View
                    </span>
                </div>
            @endif
        </td>
        <td>{{ $application->created_at->format('d-M-Y, h:i') }}</td>

        <td>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-sm btn-danger actionHandler"
                    {{ isSuperAdmin() ? ($application->status != 'submitted' ? 'disabled' : '') : 'disabled' }}
                    data-action="delete" data-url="{{ route('admin.application.destroy', $application->uuid) }}"> <i
                        class="ri-delete-bin-line"></i></button>
            </div>
        </td>

    </tr>
@empty
    <tr>
        <td colspan="9" class="p-4 text-center">No Data Available !</td>
    </tr>
@endforelse
