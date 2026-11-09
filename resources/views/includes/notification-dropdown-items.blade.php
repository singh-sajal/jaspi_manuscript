@forelse ($notifications as $notification)
    @php
        $data = $notification->data ?? [];
        $isUnread = is_null($notification->read_at);
        $severity = $data['severity'] ?? 'info';
        $color = [
            'info' => 'primary',
            'success' => 'success',
            'warning' => 'warning',
            'danger' => 'danger',
        ][$severity] ?? 'primary';
    @endphp

    <a href="{{ route($routePrefix . '.notifications.open', $notification->id) }}"
        class="notification-dropdown-item {{ $isUnread ? 'is-unread' : '' }}">
        <span class="notification-icon text-{{ $color }} bg-{{ $color }}-subtle">
            <i class="{{ $data['icon'] ?? 'ri-notification-3-line' }}"></i>
        </span>

        <span class="notification-content">
            <span class="notification-title">{{ $data['title'] ?? 'Notification' }}</span>
            <span class="notification-message">{{ \Illuminate\Support\Str::limit($data['message'] ?? '', 100) }}</span>
            <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
        </span>

        @if ($isUnread)
            <span class="notification-dot"></span>
        @endif
    </a>
@empty
    <div class="notification-empty">
        <i class="ri-notification-off-line"></i>
        <p class="mb-0">No notifications yet.</p>
    </div>
@endforelse
