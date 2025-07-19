@forelse ($records as $record)
    <tr>




        <td class="fw-bold">
            {{ $record->name ?? '---' }}
        </td>



        <td>{{ $record->created_at->format('d-M-Y, h:i, A') }}</td>

        <td>
            <div class="btn-group">


                <button class="btn btn-sm btn-danger actionHandler" data-action="delete"
                    data-url="{{ route('admin.permissions.destroy', $record->id) }}"> <i
                        class="ri-delete-bin-line"></i></button>

            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="3" class="p-4 text-center">No Data AVailable !</td>
    </tr>
@endforelse
