<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Http\Controllers\Notification\BaseNotificationController;

class NotificationController extends BaseNotificationController
{
    protected function guard(): string
    {
        return 'admin';
    }

    protected function routePrefix(): string
    {
        return 'admin';
    }

    protected function layout(): string
    {
        return 'admin.app';
    }
}
