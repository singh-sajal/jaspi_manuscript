<?php

namespace App\Http\Controllers\Author\Notification;

use App\Http\Controllers\Notification\BaseNotificationController;

class AuthorNotificationController extends BaseNotificationController
{
    protected function guard(): string
    {
        return 'web';
    }

    protected function routePrefix(): string
    {
        return 'author';
    }

    protected function layout(): string
    {
        return 'author.app';
    }
}
