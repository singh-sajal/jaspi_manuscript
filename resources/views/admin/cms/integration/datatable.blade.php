@forelse ($records as $record)
    <tr>

        <td>{{ $record->id }}</td>

        <td>
            <span class="fw-bold">{{ $record->name }}</span>
        </td>

        <td>{{ $record->created_at->format('d-M-Y, h:i') }}</td>

        <td>
            <div class="btn-group">

                @can('integration.update')
                    <button class="btn btn-sm btn-secondary btn-label actionHandler"
                        data-url="{{ route('admin.integration.edit', $record->id) }}">
                        <i class="ri-edit-box-line"></i>
                    </button>
                @endcan
                @can('integration.delete')
                    <button class="btn btn-sm btn-danger actionHandler" data-action="delete"
                        data-url="{{ route('admin.integration.destroy', $record->id) }}"> <i
                            class="ri-delete-bin-line"></i></button>
                @endcan
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="p-4 text-center">No Data Available !</td>
    </tr>
@endforelse