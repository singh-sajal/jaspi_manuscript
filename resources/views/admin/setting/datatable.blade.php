@forelse ($records as $row)
    <tr>

        {{-- Index --}}
        <td>
            <span class="badge badge-soft-secondary">
                {{ $loop->iteration }}
            </span>
        </td>


        {{-- Title --}}
        <td>
            <span class="badge badge-soft-primary">
                {{ $row->title }}
            </span>
        </td>

        {{-- Image --}}
        <td>
            @if ($row->img)
                <img src="{{ asset($row->img) }}" class="avatar avatar-sm rounded" alt="Setting Image">
            @else
                <span class="badge badge-soft-dark">No Image</span>
            @endif
        </td>




        {{-- Description (Limited like DataTables) --}}
        <td>
            <span class="badge badge-soft-secondary">
                {{ \Illuminate\Support\Str::limit($row->description, 50) }}
            </span>
        </td>

        {{-- Status --}}
        {{-- <td>
            @if ($row->status == 1)
                <span class="badge badge-soft-success">Active</span>
            @else
                <span class="badge badge-soft-danger">Inactive</span>
            @endif
        </td> --}}

        {{-- Key --}}
        <td>
            <span class="badge badge-soft-primary">
                {{ $row->key }}
            </span>
        </td>





        {{-- Actions --}}
        <td>
            <div class="btn-group" role="group">

                {{-- View --}}
                <button class="btn btn-sm btn-info actionHandler"
                    data-url="{{ route('admin.settings.show', $row->id) }}">
                    <i class="ri-eye-line"></i>
                </button>
                {{-- Edit --}}
                <button class="btn btn-sm btn-primary actionHandler"
                    data-url="{{ route('admin.settings.edit', $row->id) }}">
                    <i class="ri-edit-box-line"></i>
                </button>

                {{-- Delete --}}
                <button class="btn btn-sm btn-danger actionHandler" data-action="delete"
                    data-url="{{ route('admin.settings.destroy', $row->id) }}">
                    <i class="ri-delete-bin-line"></i>
                </button>



            </div>
        </td>

    </tr>

@empty
    <tr>
        <td colspan="7" class="text-center p-4">
            <span class="badge badge-soft-danger">No Settings Found</span>
        </td>
    </tr>
@endforelse
