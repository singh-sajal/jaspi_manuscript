@forelse ($queryRows as $row)
    <tr>


        <td>
            <div class="d-flex align-items-center">
                {{-- Add the name and username into flex column the username a slight smaller --}}
                <a class="d-flex flex-column" href="#">
                    <span class="text-dark fw-bold">{{ $row->name }}</span>
                    <span class="text-muted">{{ $row->phone ?? '---' }}</span>
                    <span class="text-muted">{{ $row->email ?? '---' }}</span>
                </a>

            </div>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <span>{{ $row->created_at->format('d-M-Y, h:i') }}</span><br>
            </div>
            <div class="d-flex align-items-center">
                <a class="d-flex flex-column" href="#">
                    <span class="{{ $row->status == 'unread' ? 'text-dark fw-bold' : ' ' }}">
                        {{ $row->subject ?? '---' }}</span>
                </a>
            </div>
        </td>

        <td>
            <div class="btn-group-sm">
                <button class="btn btn-sm btn-secondary  btn-label actionHandler" 
                data-url="{{ route('admin.query.show', $row->id) }}"> <i
                        class="ri-eye-line"></i>
                    </button>
               
                        {{-- <button class="btn btn-sm btn-secondary btn-label actionHandler" 
                data-url="{{ route('admin.author.edit', $row->uuid) }}">
                    <i class="ri-edit-box-line"></i>
                </button> --}}
                <button class="btn btn-sm btn-danger actionHandler" data-action="delete" {{-- data-url="{{ route('admin.author.destroy', $row->uuid) }}" --}}> <i
                        class="ri-delete-bin-line"></i></button>

               
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="p-4 text-center">No Data Available !</td>
    </tr>
@endforelse
