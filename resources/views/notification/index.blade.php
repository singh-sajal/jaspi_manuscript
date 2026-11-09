@extends($layout)

@section('title', 'Notifications')
@section('breadcrumb', 'Notifications')
@section('page-title', 'Notifications')

@section('breadcrumb-button')
    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if ($unreadCount > 0)
            <button type="button" class="btn btn-sm btn-primary" data-notification-mark-all
                data-url="{{ route($routePrefix . '.notifications.read-all') }}">
                <i class="ri-check-double-line me-1"></i> Mark all as read
            </button>
        @endif

        @if ($readCount > 0)
            <button type="button" class="btn btn-sm btn-light" data-notification-clear-read
                data-url="{{ route($routePrefix . '.notifications.clear-read') }}">
                <i class="ri-delete-bin-6-line me-1"></i> Clear read
            </button>
        @endif
    </div>
@endsection

@section('content')
    <div class="card" data-notification-page>
        <div class="card-header border-0 pb-0">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ $filter !== 'unread' ? 'active' : '' }}"
                        href="{{ route($routePrefix . '.notifications.index', array_filter(['filter' => 'all', 'category' => $category, 'search' => $search])) }}">
                        All
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $filter === 'unread' ? 'active' : '' }}"
                        href="{{ route($routePrefix . '.notifications.index', array_filter(['filter' => 'unread', 'category' => $category, 'search' => $search])) }}">
                        Unread
                        @if ($unreadCount > 0)
                            <span class="badge bg-primary ms-1">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" action="{{ route($routePrefix . '.notifications.index') }}" class="row g-2 align-items-end">
                <input type="hidden" name="filter" value="{{ $filter }}">

                <div class="col-md-5">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}"
                        placeholder="Search notifications...">
                </div>

                <div class="col-md-4">
                    <label class="form-label mb-1">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All categories</option>
                        @foreach ($categories as $item)
                            <option value="{{ $item }}" {{ $category === $item ? 'selected' : '' }}>
                                {{ ucfirst($item) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="ri-filter-3-line me-1"></i> Filter
                    </button>
                    <a href="{{ route($routePrefix . '.notifications.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
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

                <div class="notification-page-item {{ $isUnread ? 'is-unread' : '' }}">
                    <span class="notification-icon text-{{ $color }} bg-{{ $color }}-subtle">
                        <i class="{{ $data['icon'] ?? 'ri-notification-3-line' }}"></i>
                    </span>

                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div class="min-w-0">
                                <a href="{{ route($routePrefix . '.notifications.open', $notification->id) }}"
                                    class="notification-title text-decoration-none d-inline-block">
                                    {{ $data['title'] ?? 'Notification' }}
                                </a>
                                <div class="notification-message mt-1">{{ $data['message'] ?? '' }}</div>
                            </div>

                            <div class="notification-page-actions">
                                @if ($isUnread)
                                    <button type="button" class="btn btn-sm btn-light" title="Mark as read"
                                        data-notification-mark-read
                                        data-url="{{ route($routePrefix . '.notifications.read', $notification->id) }}">
                                        <i class="ri-check-line"></i>
                                    </button>
                                @endif

                                <button type="button" class="btn btn-sm btn-light text-danger" title="Delete"
                                    data-notification-delete
                                    data-url="{{ route($routePrefix . '.notifications.destroy', $notification->id) }}">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                            <span class="badge bg-light text-dark">
                                {{ ucfirst($notification->category ?? 'system') }}
                            </span>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            @if ($isUnread)
                                <span class="badge bg-primary">New</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="notification-empty py-5">
                    <i class="ri-notification-off-line"></i>
                    <h6 class="mt-3 mb-1">No notifications found</h6>
                    <p class="mb-0 text-muted">
                        {{ $filter === 'unread' ? 'You have read everything.' : 'Notifications will appear here as work moves through the system.' }}
                    </p>
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="card-footer bg-white">
                {{ $notifications->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@endsection
