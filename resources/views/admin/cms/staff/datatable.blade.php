@forelse ($records as $record)
    <tr>


        <td>
            <div class="d-flex align-items-center">

                <img class="avatar avatar-md me-2"
                    src="{{ asset($record->avatar ?? 'admin/assets/images/avatar/1.png') }}" alt="{{ $record->name }}">

                {{-- Add the name and username into flex column the username a slight smaller --}}
                <a class="d-flex flex-column" href="{{ route('admin.profile', $record->uuid) }}">
                    <span class="text-dark fw-bold">{{ $record->name }}</span>
                    <span class="text-muted">{{ $record->username ?? '---' }}</span>
                </a>

            </div>
        </td>

        <td>
            {{ $record->getRoleNames()->isNotEmpty() ? implode(', ', $record->getRoleNames()->toArray()) : '--' }}

        </td>

        <td>
            <div class="d-flex align-items-center">
                <span>{{ $record->email ?? '---' }}</span>
                <i class="ri-checkbox-circle-fill text-info ms-1"></i>
            </div>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <span>{{ $record->phone ?? '---' }}</span>
                <i class="ri-checkbox-circle-fill text-info ms-1"></i>
            </div>
        </td>

        <td>
            @if ($record->password == null)
                <button
                    class="badge badge-xs bg-dark {{ authUser('admin')->can('staff.generate-credentials') ? 'actionHandler' : '' }}"
                    data-url="{{ route('admin.staff.generateCredentials', $record->uuid) }}">Allow Login</button>
            @else
                <button
                    class="badge badge-xs {{ authUser('admin')->can('staff.toggle-status') ? 'actionHandler' : '' }} bg-{{ $record->status == '1' ? 'success' : 'danger' }}"
                    data-action="togglestatus"
                    data-url="{{ route('admin.staff.toggleStatus', [
                        'uuid' => $record->uuid,
                        'column' => 'status',
                        'status' => $record->status == '1' ? 'active' : 'inactive',
                    ]) }}">
                    {{ $record->status == '1' ? 'Active' : 'Inactive' }}</button>
            @endif
        </td>
        <td>{{ $record->created_at->format('d-M-Y, h:i') }}</td>


        <td>
            <div class="btn-group">

                @can('staff.update')
                    <button class="btn btn-sm btn-secondary btn-label actionHandler"
                        data-url="{{ route('admin.staff.edit', $record->uuid) }}">
                        <i class="ri-edit-box-line"></i>
                    </button>
                @endcan
                @can('staff.delete')
                    <button class="btn btn-sm btn-danger actionHandler" data-action="delete"
                        data-url="{{ route('admin.staff.destroy', $record->uuid) }}"> <i
                            class="ri-delete-bin-line"></i></button>
                @endcan
                @can('staff.generate-credentials')
                    @if ($record->password != null)
                        <button class="btn btn-sm btn-info actionHandler"
                            data-url="{{ route('admin.staff.generateCredentials', $record->uuid) }}">
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
