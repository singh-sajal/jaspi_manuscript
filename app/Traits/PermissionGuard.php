<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

trait PermissionGuard
{
    /**
     * Get all permissions associated with the user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null  $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getPermissions($user = null)
    {
        $user = $user ?: authUser('admin');

        return $user->getAllPermissions();
    }

    /**
     * Filter permissions based on a category.
     *
     * @param  string  $category
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function filterPermissions($category)
    {
        return $this->getPermissions()
            ->filter(function ($permission) use ($category) {
                return Str::startsWith($permission->name, "$category.");
            });
    }

    /**
     * Check if the user has access to the index action.
     *
     * @param  string  $module
     * @return bool
     */
    protected function canAccessPage($category)
    {
        $categoryPermissions = $this->filterPermissions($category);

        if ($categoryPermissions->isNotEmpty() || isSuperAdmin()) {
            return true;
        }
        return false;
    }
    protected function check($permission)
    {
        if (!authUser('admin')->can($permission)) {
            abort(403, 'Access Denied');
        }
    }
}
