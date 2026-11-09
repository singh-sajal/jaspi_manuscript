@forelse ($records as $record)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $record->email }}</td>

        {{-- Invitation count --}}
        <td class="text-center">
            @php
                $count = $record->invitation_count;
                $badge = $count >= 3 ? 'danger' : ($count >= 2 ? 'warning' : 'success');
            @endphp
            <span class="badge badge-rounded badge-{{ $badge }}">
                {{ $count }} / 3
            </span>
        </td>

        <td>{{ $record->last_invited_at?->format('d-M-Y, h:i A') ?? '—' }}</td>
        <td>{{ $record->created_at->format('d-M-Y, h:i A') }}</td>

        <td>
            <div class="btn-group">

                @if ($record->invitation_count < 3)
                    <form action="{{ route('admin.inviteReviewer.resend', $record->id) }}"
                          method="POST" class="d-inline-block"
                          onsubmit="return confirm('Resend invitation to {{ $record->email }}?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-info"
                                title="Resend Invitation ({{ 3 - $record->invitation_count }} remaining)">
                            <i class="ri-arrow-right-up-box-fill"></i>
                        </button>
                    </form>
                @else
                    <button class="btn btn-sm btn-secondary" disabled
                            title="Max 3 invitations already sent">
                        <i class="ri-mail-forbid-line"></i>
                    </button>
                @endif

                <button class="btn btn-sm btn-danger actionHandler"
                        data-action="delete"
                        title="Delete this record"
                        data-url="{{ route('admin.inviteReviewer.destroy', $record->id) }}">
                    <i class="ri-delete-bin-line"></i>
                </button>

            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="p-4 text-center">No Data Available!</td>
    </tr>
@endforelse
