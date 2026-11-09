<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const GUARD = 'admin';

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $inactive = Permission::firstOrCreate([
            'name' => 'application.inactive',
            'guard_name' => self::GUARD,
        ]);

        $reviewDeadline = Permission::firstOrCreate([
            'name' => 'application.reviewDeadline',
            'guard_name' => self::GUARD,
        ]);

        // The superadmin passes every ability through Gate::before, but keep
        // the permission attached so the role/permission screens agree.
        $this->grant('superadmin', $inactive);
        $this->grant('superadmin', $reviewDeadline);

        // Handling editors still need the review-deadline tool; assistant
        // editors may also need to chase reviewers.
        $this->grant('Editor', $reviewDeadline);
        $this->grant('assitantEditor', $reviewDeadline);

        // These roles must never see the admin-only inactive list, and the
        // technical editor must not see review deadlines.
        foreach (['technicalEditor', 'Reviewer', 'Publisher'] as $roleName) {
            $this->revoke($roleName, $inactive);
            $this->revoke($roleName, $reviewDeadline);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::where('guard_name', self::GUARD)
            ->whereIn('name', ['application.inactive', 'application.reviewDeadline'])
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function grant(string $roleName, Permission $permission): void
    {
        $role = Role::where('name', $roleName)
            ->where('guard_name', self::GUARD)
            ->first();

        if ($role && !$role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }
    }

    private function revoke(string $roleName, Permission $permission): void
    {
        $role = Role::where('name', $roleName)
            ->where('guard_name', self::GUARD)
            ->first();

        if ($role && $role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
        }
    }
};
