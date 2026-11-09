<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Author;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Create an in-app notification for a notifiable (Admin or Author).
     *
     * The payload is stored inside the standard Laravel notifications.data
     * column so the existing notifications table can be reused as-is.
     *
     * @param  \Illuminate\Notifications\Notifiable  $notifiable
     * @param  array{category?: string, type?: string, title?: string, message?: string, url?: string|null, icon?: string, severity?: string, meta?: array}  $payload
     */
    public function send($notifiable, array $payload): ?DatabaseNotification
    {
        if (!$notifiable || !$this->isActive($notifiable)) {
            return null;
        }

        return $notifiable->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => $payload['type'] ?? 'in_app',
            'category' => $payload['category'] ?? 'system',
            'data' => [
                'title' => $payload['title'] ?? 'Notification',
                'message' => $payload['message'] ?? '',
                'url' => $payload['url'] ?? null,
                'icon' => $payload['icon'] ?? 'ri-notification-3-line',
                'severity' => $payload['severity'] ?? 'info',
                'meta' => $payload['meta'] ?? [],
            ],
        ]);
    }

    /**
     * Send the same notification to every active admin, optionally excluding
     * the admin who performed the action.
     */
    public function sendToAdmins(array $payload, ?int $excludeAdminId = null): int
    {
        $query = Admin::query()->where('status', 1);

        if ($excludeAdminId) {
            $query->where('id', '!=', $excludeAdminId);
        }

        $sent = 0;

        foreach ($query->get() as $admin) {
            if ($this->send($admin, $payload)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Send a notification to every active admin holding the given role.
     */
    public function sendToRole(string $role, array $payload, ?int $excludeAdminId = null): int
    {
        $query = Admin::query()
            ->where('status', 1)
            ->whereHas('roles', function ($query) use ($role) {
                $query->where('name', $role)->where('guard_name', 'admin');
            });

        if ($excludeAdminId) {
            $query->where('id', '!=', $excludeAdminId);
        }

        $sent = 0;

        foreach ($query->get() as $admin) {
            if ($this->send($admin, $payload)) {
                $sent++;
            }
        }

        return $sent;
    }

    public function sendToAuthor(?Author $author, array $payload): ?DatabaseNotification
    {
        return $this->send($author, $payload);
    }

    /**
     * status is a boolean column on both admins and authors. Treat a missing
     * value as active so legacy accounts are not silently skipped.
     */
    private function isActive($notifiable): bool
    {
        if (!isset($notifiable->status)) {
            return true;
        }

        return filter_var($notifiable->status, FILTER_VALIDATE_BOOLEAN);
    }
}
