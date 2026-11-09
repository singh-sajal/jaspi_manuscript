<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BaseNotificationController extends Controller
{
    abstract protected function guard(): string;

    abstract protected function routePrefix(): string;

    abstract protected function layout(): string;

    protected function user()
    {
        return authUser($this->guard());
    }

    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $category = $request->query('category');
        $search = trim((string) $request->query('search'));

        $query = $this->user()->notifications()->latest();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($search !== '') {
            $query->where('data', 'like', '%' . $search . '%');
        }

        $notifications = $query->paginate(20)->withQueryString();

        $categories = $this->user()->notifications()
            ->whereNotNull('category')
            ->reorder()
            ->pluck('category')
            ->unique()
            ->sort()
            ->values();

        $unreadCount = $this->user()->unreadNotifications()->count();
        $readCount = $this->user()->readNotifications()->count();

        return view('notification.index', [
            'notifications' => $notifications,
            'categories' => $categories,
            'filter' => $filter,
            'category' => $category,
            'search' => $search,
            'unreadCount' => $unreadCount,
            'readCount' => $readCount,
            'layout' => $this->layout(),
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function latest()
    {
        $notifications = $this->user()->notifications()->latest()->limit(8)->get();

        return response()->json([
            'count' => $this->user()->unreadNotifications()->count(),
            'html' => view('includes.notification-dropdown-items', [
                'notifications' => $notifications,
                'routePrefix' => $this->routePrefix(),
            ])->render(),
        ]);
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => $this->user()->unreadNotifications()->count(),
        ]);
    }

    public function open($id)
    {
        $notification = $this->user()->notifications()->where('id', $id)->firstOrFail();

        $notification->markAsRead();

        $url = data_get($notification->data, 'url');

        return redirect($url ?: route($this->routePrefix() . '.notifications.index'));
    }

    public function markAsRead($id)
    {
        $notification = $this->user()->notifications()->where('id', $id)->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'status' => '200',
            'msg' => 'Notification marked as read.',
            'count' => $this->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllAsRead()
    {
        $this->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'status' => '200',
            'msg' => 'All notifications marked as read.',
            'count' => 0,
        ]);
    }

    public function destroy($id)
    {
        $notification = $this->user()->notifications()->where('id', $id)->firstOrFail();

        $notification->delete();

        return response()->json([
            'status' => '200',
            'msg' => 'Notification deleted.',
            'count' => $this->user()->unreadNotifications()->count(),
        ]);
    }

    public function clearRead()
    {
        $deleted = $this->user()->readNotifications()->delete();

        return response()->json([
            'status' => '200',
            'msg' => "{$deleted} read notification(s) cleared.",
            'count' => $this->user()->unreadNotifications()->count(),
        ]);
    }
}
