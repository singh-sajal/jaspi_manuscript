@forelse ($applications as $application)
    <tr>
        <td>
            <div class="d-flex align-items-center">

                {{-- <img class="avatar avatar-md me-2"
                    src="{{ asset($application->avatar ?? 'admin/assets/images/avatar/1.png') }}" alt="{{ $application->name }}"> --}}

                {{-- Add the name and username into flex column the username a slight smaller --}}
                <a class="d-flex flex-column" href="{{ route('author.application.show', $application->uuid) }}">
                    <span class="text-dark fw-bold">{{ $application->title }}</span>
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
        <td>
            @if ($application->status == 'incomplete')
                @if ($application->isReadyToSubmit())
                    <span class="badge bg-danger actionHandler" data-action="togglestatus"
                        data-url="{{ route('author.application.toggleStatus', [
                            'uuid' => $application->uuid,
                            'column' => 'status',
                            'status' => 'submitted',
                        ]) }}">Ready
                        To Submit</span>
                @else
                    <a href="{{ route('author.application.show', $application->uuid) }}" class="badge bg-danger">
                        {{ $application->status }}
                    </a>
                @endif
            @else
                <div class="d-flex align-items-center ms-3">
                    <span
                        class="badge bg-{{ $application->status === 'submitted' ? 'success' : 'dark' }}">{{ $application->status }}</span>
                </div>
            @endif
            @if ($application->status == 'published')
                <div class="ms-5 my-3">
                    <span class="fs-12 text-dark text-decoration-underline actionHandler px-1 text-center"
                        data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                        data-file-url="{{ getFileUrl($application->data['file_url'] ?? '') }}"
                        data-handler="openFilePreview" target="_blank" title="View Published File">
                        View
                    </span>
                </div>
            @endif
        </td>



        <td>
            <div class="d-flex align-items-center">

                <img class="avatar avatar-sm me-2"
                    src="{{ asset($application->assignee->avatar ?? 'admin/assets/images/avatar/1.png') }}"
                    alt="avatar">
                <span class="text-dark fw-bold">{{ $application->assignee->name ?? '---' }}</span>
            </div>
        </td>


        <td>{{ $application->created_at->format('d-M-Y, h:i') }}</td>

        <td>
            <div class="btn-group">
                {{-- <a class="btn btn-sm btn-secondary" href="{{ route('admin.author.edit', $author->uuid) }}"> <i
                        class="ri-edit-box-line"></i></a> --}}
                <a class="btn btn-sm btn-secondary btn-label"
                    href="{{ route('author.application.show', $application->uuid) }}">
                    {{-- <i class="ri-edit-box-line"></i> --}}
                    <i class="ri-eye-line"></i>
                </a>
                @if ($application->status === 'incomplete')
                    <button class="btn btn-sm btn-warning actionHandler" data-action="delete"
                        data-url="{{ route('author.application.destroy', $application->uuid) }}"> <i
                            class="ri-delete-bin-line"></i></button>
                @endif
                {{-- @if ($application->status !== 'incomplete')
                    <button class="btn btn-sm btn-light" data-action="delete"
                        data-url="{{ route('author.application.destroy', $application->uuid) }}"> <i
                            class="ri-delete-bin-line"
                            style="border: 1px #d3c1c1 solid; border-radius: 4px;
                                background-color: #d3d3d3; color: #a9a9a9; cursor: not-allowed;"></i></button>
                @else
                    <button class="btn btn-sm btn-danger actionHandler" data-action="delete"
                        data-url="{{ route('author.application.destroy', $application->uuid) }}"> <i
                            class="ri-delete-bin-line"></i></button>
                @endif --}}
            </div>
        </td>


    </tr>
@empty
    <tr>
        <td colspan="9" class="p-4 text-center">No Data Available !</td>
    </tr>
@endforelse
