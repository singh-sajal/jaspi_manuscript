@forelse ($records as $record)
    <tr>

        <td class="fw-bold">
            {{ $record->name ?? '---' }}
        </td>
        <td>
            {{ count($record->permissions ?? 0) }}
        </td>


        <td>{{ $record->created_at->format('d-M-Y, h:i, A') }}</td>

        <td>
            <div class="btn-group">

                <a class="btn btn-sm btn-info" href="{{ route('admin.roles.show', $record->id) }}"> <i
                        class="ri-shield-keyhole-line"></i></a>

                <button class="btn btn-sm btn-secondary actionHandler"
                    data-url="{{ route('admin.roles.edit', $record->id) }}"> <i class="ri-edit-box-line"></i></button>
                <button class="btn btn-sm btn-danger actionHandler" data-action="delete"
                    data-url="{{ route('admin.roles.destroy', $record->id) }}"> <i
                        class="ri-delete-bin-line"></i></button>

            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="p-4 text-center">No Data AVailable !</td>
    </tr>
@endforelse
