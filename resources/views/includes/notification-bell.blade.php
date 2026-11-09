@php
    $user = authUser($guard);
    $notifications = $user ? $user->notifications()->latest()->limit(8)->get() : collect();
    $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    $dropdownId = 'notificationDropdown' . ucfirst($guard);
@endphp

@if ($user)
    <li class="nav-item dropdown notification_dropdown">
        <a class="nav-link {{ $textClass ?? 'text-white' }} position-relative" href="javascript:void(0);"
            id="{{ $dropdownId }}Toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false"
            title="Notifications" aria-label="Notifications">
            <i class="ri-notification-3-line fs-20"></i>
            <span class="notification-badge {{ $unreadCount > 0 ? '' : 'd-none' }}" data-notification-count>
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        </a>

        <div class="dropdown-menu dropdown-menu-end notification-dropdown-menu"
            aria-labelledby="{{ $dropdownId }}Toggle" data-notification-dropdown
            data-latest-url="{{ route($routePrefix . '.notifications.latest') }}"
            data-unread-url="{{ route($routePrefix . '.notifications.unread-count') }}">
            <div class="notification-dropdown-header">
                <h6 class="mb-0">Notifications</h6>
                <button type="button"
                    class="btn btn-link btn-sm p-0 text-primary text-decoration-none {{ $unreadCount > 0 ? '' : 'd-none' }}"
                    data-notification-mark-all
                    data-url="{{ route($routePrefix . '.notifications.read-all') }}">
                    Mark all read
                </button>
            </div>

            <div class="notification-dropdown-body" data-notification-list>
                @include('includes.notification-dropdown-items', [
                    'notifications' => $notifications,
                    'routePrefix' => $routePrefix,
                ])
            </div>

            <div class="notification-dropdown-footer">
                <a href="{{ route($routePrefix . '.notifications.index') }}">View all notifications</a>
            </div>
        </div>
    </li>
@endif
