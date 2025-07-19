@forelse ($authors as $author)
    <tr>


        <td>
            <div class="d-flex align-items-center">

                <img class="avatar avatar-md me-2"
                    src="{{ asset($author->avatar ?? 'admin/assets/images/avatar/1.png') }}" alt="{{ $author->name }}">

                {{-- Add the name and username into flex column the username a slight smaller --}}
                <a class="d-flex flex-column" href="{{ route('admin.getAuthorProfile', $author->uuid) }}">
                    <span class="text-dark fw-bold">{{ $author->name }}</span>
                    <span class="text-muted">{{ $author->username ?? '---' }}</span>
                </a>

            </div>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <span>{{ $author->email ?? '---' }}</span>
                <i class="ri-checkbox-circle-fill text-info ms-1"></i>
            </div>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <span>{{ $author->phone ?? '---' }}</span>
                <i class="ri-checkbox-circle-fill text-info ms-1"></i>
            </div>
        </td>
        <td>
            @if ($author->password == null)
                <button class="badge badge-xs bg-dark actionHandler"
                    data-url="{{ route('admin.author.generateCredentials', $author->uuid) }}">Allow Login</button>
            @else
                <button class="badge badge-xs actionHandler bg-{{ $author->status == '1' ? 'success' : 'danger' }}"
                    data-action="togglestatus"
                    data-url="{{ route('admin.author.toggleStatus', [
                        'uuid' => $author->uuid,
                        'column' => 'status',
                        'status' => $author->status == '1' ? 'active' : 'inactive',
                    ]) }}">
                    {{ $author->status == '1' ? 'Active' : 'Inactive' }}</button>
            @endif
        </td>
        {{-- <td><span
                class="badge badge-xs actionHandler bg-{{ $author->is_twofactor_enabled == '1' ? 'success' : 'danger' }}"
                data-action="togglestatus"
                data-url="{{ route('admin.consultants.togglestatus', [
                    'uuid' => $author->uuid,
                    'column' => 'is_twofactor_enabled',
                    'status' => $author->is_twofactor_enabled == '1' ? 'active' : 'inactive',
                ]) }}">
                {{ $author->is_twofactor_enabled == '1' ? 'Enabled' : 'Disabled' }}</span>
        </td> --}}
        <td>{{ $author->created_at->format('d-M-Y, h:i') }}</td>


        <td>
            <div class="btn-group-sm">
                {{-- <a class="btn btn-sm btn-secondary" href="{{ route('admin.author.edit', $author->uuid) }}"> <i
                        class="ri-edit-box-line"></i></a> --}}
                <button class="btn btn-sm btn-secondary btn-label actionHandler"
                    data-url="{{ route('admin.author.edit', $author->uuid) }}">
                    <i class="ri-edit-box-line"></i>
                </button>
                <button class="btn btn-sm btn-danger actionHandler" data-action="delete"
                    data-url="{{ route('admin.author.destroy', $author->uuid) }}"> <i
                        class="ri-delete-bin-line"></i></button>
                {{-- @if ($author->password != null)
                    <button class="btn btn-sm btn-info actionHandler"
                        data-url="{{ route('admin.author.generatecredentials', $author->uuid) }}">
                        <i class="ri-lock-password-line"></i>
                    </button>
                @endif --}}
                @can('staff.generate-credentials')
                    @if ($author->password != null)
                        <button class="btn btn-sm btn-info actionHandler"
                            data-url="{{ route('admin.author.generateCredentials', $author->uuid) }}">
                            <i class="ri-lock-password-line"></i>
                        </button>
                    @endif
                @endcan
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="p-4 text-center">No Data Available !</td>
    </tr>
@endforelse
