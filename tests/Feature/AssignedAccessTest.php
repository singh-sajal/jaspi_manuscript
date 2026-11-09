<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssignedAccessTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(array $attributes = []): Admin
    {
        $suffix = uniqid();

        return Admin::create(array_merge([
            'username' => 'assigned.access.' . $suffix,
            'name' => 'Assigned Access Test',
            'email' => 'assigned.access.' . $suffix . '@example.test',
            'phone' => '7' . substr((string) time(), -6) . random_int(100, 999),
            'password' => Hash::make('secret123'),
            'status' => true,
            'is_admin' => 0,
        ], $attributes));
    }

    private function assignRole(Admin $admin, string $role): void
    {
        $admin->assignRole(Role::where('name', $role)->where('guard_name', 'admin')->firstOrFail());
    }

    private function makeApplication(string $status, array $attributes = []): Application
    {
        $suffix = uniqid();

        return Application::create(array_merge([
            'application_id' => 'ACCESS-' . $suffix,
            'title' => 'Assigned access test ' . $suffix,
            'submission_type' => 'new_submission',
            'status' => $status,
        ], $attributes));
    }

    public function test_technical_editor_can_open_content_only_when_assigned_to_them()
    {
        $technicalEditor = $this->createAdmin();
        $this->assignRole($technicalEditor, 'technicalEditor');

        $assigned = $this->makeApplication('submitted', [
            'assigned_to_id' => $technicalEditor->id,
            'description' => 'Visible to the assigned technical editor.',
        ]);

        $unassigned = $this->makeApplication('submitted', [
            'description' => 'Hidden from an unassigned technical editor.',
        ]);

        $this->actingAs($technicalEditor, 'admin')
            ->get(route('admin.application.show', $assigned->uuid))
            ->assertOk()
            ->assertSee('Visible to the assigned technical editor.')
            ->assertDontSee('Hidden from an unassigned technical editor.');

        $this->actingAs($technicalEditor, 'admin')
            ->get(route('admin.application.show', $unassigned->uuid))
            ->assertOk()
            ->assertDontSee('Hidden from an unassigned technical editor.');
    }

    public function test_assign_publisher_list_excludes_admin_accounts()
    {
        $admin = $this->createAdmin([
            'is_admin' => 1,
            'name' => 'Assigning Super Admin',
        ]);
        $this->assignRole($admin, 'superadmin');

        $publisher = $this->createAdmin([
            'name' => 'Visible Publisher Candidate',
        ]);
        $this->assignRole($publisher, 'Publisher');

        $adminPublisher = $this->createAdmin([
            'is_admin' => 1,
            'name' => 'Hidden Admin Publisher Candidate',
        ]);
        $this->assignRole($adminPublisher, 'Publisher');

        $application = $this->makeApplication('pre-accepted');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.application.assignPublisher', $application->uuid))
            ->assertOk()
            ->assertSee('Visible Publisher Candidate')
            ->assertDontSee('Hidden Admin Publisher Candidate');
    }
}
