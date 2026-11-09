<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Application;
use App\Models\Author;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InactiveAssigneeMessageTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(array $attributes = []): Admin
    {
        $suffix = uniqid();

        return Admin::create(array_merge([
            'username' => 'assignee.test.' . $suffix,
            'name' => 'Assignee Test',
            'email' => 'assignee.test.' . $suffix . '@example.test',
            'phone' => '6' . substr((string) time(), -6) . random_int(100, 999),
            'password' => Hash::make('secret123'),
            'status' => true,
        ], $attributes));
    }

    private function assignRole(Admin $admin, string $role): void
    {
        $admin->assignRole(Role::where('name', $role)->where('guard_name', 'admin')->firstOrFail());
    }

    private function createAuthor(array $attributes = []): Author
    {
        $suffix = uniqid();

        return Author::create(array_merge([
            'username' => 'assignee.author.' . $suffix,
            'name' => 'Proofreading Author',
            'lname' => 'Test',
            'email' => 'assignee.author.' . $suffix . '@example.test',
            'phone' => '3' . substr((string) time(), -6) . random_int(100, 999),
            'password' => Hash::make('secret123'),
            'status' => true,
        ], $attributes));
    }

    private function makeApplication(string $status, array $attributes = []): Application
    {
        $suffix = uniqid();

        return Application::create(array_merge([
            'application_id' => 'TEST-' . $suffix,
            'title' => 'Inactive assignee test ' . $suffix,
            'submission_type' => 'new_submission',
            'status' => $status,
        ], $attributes));
    }

    public function test_inactive_internal_and_external_reviewers_are_flagged_in_the_assigned_to_column()
    {
        $viewer = $this->createAdmin(['is_admin' => 1, 'name' => 'Viewer Admin']);
        $this->assignRole($viewer, 'superadmin');

        $internal = $this->createAdmin([
            'name' => 'Inactive Internal Reviewer',
            'status' => false,
            'rev_type' => 'internal',
        ]);
        $this->assignRole($internal, 'Reviewer');

        $external = $this->createAdmin([
            'name' => 'Inactive External Reviewer',
            'status' => false,
            'rev_type' => 'external',
        ]);
        $this->assignRole($external, 'Reviewer');

        $this->makeApplication('under review', [
            'assigned_to_id' => $internal->id,
            'assigned_to_ex_id' => $external->id,
        ]);

        $response = $this->actingAs($viewer, 'admin')->get(route('admin.application.index'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $html = $response->json('data');

        $this->assertStringContainsString('Inactive internal reviewer. Please select different staff.', $html);
        $this->assertStringContainsString('Inactive external reviewer. Please select different staff.', $html);
    }

    public function test_inactive_staff_is_flagged_in_the_assigned_to_column()
    {
        $viewer = $this->createAdmin(['is_admin' => 1, 'name' => 'Viewer Admin']);
        $this->assignRole($viewer, 'superadmin');

        $editor = $this->createAdmin([
            'name' => 'Inactive Handling Editor',
            'status' => false,
        ]);
        $this->assignRole($editor, 'Editor');

        $this->makeApplication('processing', [
            'assigned_to_id' => $editor->id,
        ]);

        $response = $this->actingAs($viewer, 'admin')->get(route('admin.application.index'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $this->assertStringContainsString(
            'Inactive staff. Please select different staff.',
            $response->json('data')
        );
    }

    public function test_proofreading_shows_the_author_in_the_assigned_to_column()
    {
        $viewer = $this->createAdmin(['is_admin' => 1, 'name' => 'Viewer Admin']);
        $this->assignRole($viewer, 'superadmin');

        $author = $this->createAuthor(['name' => 'Proofreading Author Name']);
        $editor = $this->createAdmin(['name' => 'Handling Editor Name']);

        $this->makeApplication('proofreading', [
            'author_id' => $author->id,
            'assigned_to_id' => $editor->id,
        ]);

        $response = $this->actingAs($viewer, 'admin')->get(route('admin.application.index'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $html = $response->json('data');

        $this->assertStringContainsString('Proofreading Author Name', $html);
        $this->assertStringContainsString('Author', $html);
        $this->assertStringNotContainsString('Handling Editor Name', $html);
    }
}
