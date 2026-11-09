<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Application;
use App\Models\ApplicationTimeline;
use App\Models\Attachment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManuscriptCategorizationTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(array $attributes = []): Admin
    {
        $suffix = uniqid();

        return Admin::create(array_merge([
            'username' => 'manuscript.category.' . $suffix,
            'name' => 'Manuscript Category Test',
            'email' => 'manuscript.category.' . $suffix . '@example.test',
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
            'application_id' => 'CAT-' . $suffix,
            'title' => 'Categorization test ' . $suffix,
            'submission_type' => 'new_submission',
            'status' => $status,
        ], $attributes));
    }

    private function addTimeline(Application $application, string $status, array $attributes = []): ApplicationTimeline
    {
        return ApplicationTimeline::create(array_merge([
            'application_id' => $application->id,
            'status' => $status,
        ], $attributes));
    }

    private function indexHtml(Admin $admin, string $type = ''): string
    {
        $query = ['perPage' => 500];

        if ($type !== '') {
            $query['type'] = $type;
        }

        $response = $this->actingAs($admin, 'admin')->get(
            route('admin.application.index', $query),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();

        return $response->json('data');
    }

    public function test_reviewer_review_moves_the_manuscript_from_active_to_completed()
    {
        $reviewer = $this->createAdmin(['rev_type' => 'internal']);
        $this->assignRole($reviewer, 'Reviewer');

        $completed = $this->makeApplication('under review', [
            'assigned_to_id' => $reviewer->id,
        ]);
        $this->addTimeline($completed, 'internal-reviewer-reviewed', [
            'assigned_to_id' => $reviewer->id,
            'assigned_by' => $reviewer->id,
        ]);

        $active = $this->makeApplication('under review', [
            'assigned_to_id' => $reviewer->id,
        ]);

        $activeHtml = $this->indexHtml($reviewer);
        $completedHtml = $this->indexHtml($reviewer, 'completed');

        $this->assertStringContainsString($active->application_id, $activeHtml);
        $this->assertStringNotContainsString($completed->application_id, $activeHtml);

        $this->assertStringContainsString($completed->application_id, $completedHtml);
        $this->assertStringNotContainsString($active->application_id, $completedHtml);
    }

    public function test_editor_final_decision_moves_the_manuscript_from_active_to_completed()
    {
        $editor = $this->createAdmin();
        $this->assignRole($editor, 'Editor');

        $completed = $this->makeApplication('editor-accepts');
        $this->addTimeline($completed, 'editor-accepts', [
            'assigned_to_id' => $editor->id,
            'assigned_by' => $editor->id,
        ]);

        $active = $this->makeApplication('processing', [
            'assigned_to_id' => $editor->id,
        ]);
        $this->addTimeline($active, 'processing', [
            'assigned_to_id' => $editor->id,
        ]);

        $activeHtml = $this->indexHtml($editor);
        $completedHtml = $this->indexHtml($editor, 'completed');

        $this->assertStringContainsString($active->application_id, $activeHtml);
        $this->assertStringNotContainsString($completed->application_id, $activeHtml);

        $this->assertStringContainsString($completed->application_id, $completedHtml);
        $this->assertStringNotContainsString($active->application_id, $completedHtml);
    }

    public function test_closed_lists_only_terminal_manuscripts_the_staff_member_handled()
    {
        $reviewer = $this->createAdmin(['rev_type' => 'internal']);
        $this->assignRole($reviewer, 'Reviewer');

        $otherReviewer = $this->createAdmin(['rev_type' => 'internal']);
        $this->assignRole($otherReviewer, 'Reviewer');

        $closed = collect(['rejected', 'published', 'withdrawn', 'duplicate'])->map(function ($status) use ($reviewer) {
            $application = $this->makeApplication($status, [
                'assigned_to_id' => $reviewer->id,
            ]);
            $this->addTimeline($application, 'processing', [
                'assigned_to_id' => $reviewer->id,
            ]);

            return $application;
        });

        $other = $this->makeApplication('rejected', [
            'assigned_to_id' => $otherReviewer->id,
        ]);
        $this->addTimeline($other, 'processing', [
            'assigned_to_id' => $otherReviewer->id,
        ]);

        $closedHtml = $this->indexHtml($reviewer, 'closed');

        foreach ($closed as $application) {
            $this->assertStringContainsString($application->application_id, $closedHtml);
        }

        $this->assertStringNotContainsString($other->application_id, $closedHtml);
    }

    public function test_published_manuscripts_are_closed_but_not_completed_without_explicit_work()
    {
        $reviewer = $this->createAdmin(['rev_type' => 'internal']);
        $this->assignRole($reviewer, 'Reviewer');

        $published = $this->makeApplication('published');
        $this->addTimeline($published, 'processing', [
            'assigned_to_id' => $reviewer->id,
        ]);

        $completedHtml = $this->indexHtml($reviewer, 'completed');
        $closedHtml = $this->indexHtml($reviewer, 'closed');

        $this->assertStringContainsString($published->application_id, $closedHtml);
        $this->assertStringNotContainsString($published->application_id, $completedHtml);
    }

    public function test_published_manuscript_with_submitted_review_is_only_closed()
    {
        $reviewer = $this->createAdmin(['rev_type' => 'internal']);
        $this->assignRole($reviewer, 'Reviewer');

        $published = $this->makeApplication('published');
        $this->addTimeline($published, 'internal-reviewer-reviewed', [
            'assigned_to_id' => $reviewer->id,
            'assigned_by' => $reviewer->id,
        ]);

        $this->assertStringNotContainsString($published->application_id, $this->indexHtml($reviewer, 'completed'));
        $this->assertStringContainsString($published->application_id, $this->indexHtml($reviewer, 'closed'));
    }

    public function test_publisher_closed_tab_includes_published_manuscripts()
    {
        $publisher = $this->createAdmin();
        $this->assignRole($publisher, 'Publisher');

        $published = $this->makeApplication('published', [
            'assigned_to_id' => $publisher->id,
        ]);
        $this->addTimeline($published, 'published', [
            'assigned_to_id' => $publisher->id,
            'assigned_by' => $publisher->id,
        ]);

        $closedHtml = $this->indexHtml($publisher, 'closed');
        $completedHtml = $this->indexHtml($publisher, 'completed');

        $this->assertStringContainsString($published->application_id, $closedHtml);
        $this->assertStringNotContainsString($published->application_id, $completedHtml);
    }

    public function test_published_manuscript_view_uses_the_latest_corrected_proof_in_closed()
    {
        $publisher = $this->createAdmin();
        $this->assignRole($publisher, 'Publisher');

        $published = $this->makeApplication('published', [
            'assigned_to_id' => $publisher->id,
        ]);

        $this->addTimeline($published, 'published', [
            'assigned_to_id' => $publisher->id,
            'assigned_by' => $publisher->id,
        ]);

        Attachment::create([
            'application_id' => $published->id,
            'file_name' => 'older-corrected-proof.pdf',
            'file_url' => 'uploads/applications/older-corrected-proof.pdf',
            'attachment_type' => 'Corrected Proof',
            'created_at' => now()->subDays(2),
        ]);

        Attachment::create([
            'application_id' => $published->id,
            'file_name' => 'latest-corrected-proof.pdf',
            'file_url' => 'uploads/applications/latest-corrected-proof.pdf',
            'attachment_type' => 'Corrected Proof',
            'created_at' => now()->subDay(),
        ]);

        // A direct-publish document may exist too, but the corrected proof wins.
        Attachment::create([
            'application_id' => $published->id,
            'file_name' => 'published-with-doi.pdf',
            'file_url' => 'uploads/applications/published-with-doi.pdf',
            'attachment_type' => 'Published with DOI',
            'created_at' => now(),
        ]);

        $closedHtml = $this->indexHtml($publisher, 'closed');

        $this->assertStringContainsString('latest-corrected-proof.pdf', $closedHtml);
        $this->assertStringNotContainsString('older-corrected-proof.pdf', $closedHtml);
        $this->assertStringNotContainsString('published-with-doi.pdf', $closedHtml);
    }

    public function test_admin_closed_tab_includes_all_terminal_statuses_without_assignment()
    {
        $admin = $this->createAdmin(['is_admin' => 1]);
        $this->assignRole($admin, 'superadmin');

        $otherStaff = $this->createAdmin();

        // Admins oversee the whole terminal archive, not only manuscripts
        // where they appear in assigned_to_id or the timeline history.
        $closed = collect(['rejected', 'published', 'withdrawn', 'duplicate'])->map(function ($status) use ($otherStaff) {
            return $this->makeApplication($status, [
                'assigned_to_id' => $otherStaff->id,
            ]);
        });

        $closedHtml = $this->indexHtml($admin, 'closed');
        $completedHtml = $this->indexHtml($admin, 'completed');

        foreach ($closed as $application) {
            $this->assertStringContainsString($application->application_id, $closedHtml);
            $this->assertStringNotContainsString($application->application_id, $completedHtml);
        }

        $duplicate = $closed->firstWhere('status', 'duplicate');
        $this->assertStringContainsString(
            $duplicate->application_id,
            $this->indexHtml($admin, 'duplicate')
        );
    }

    public function test_technical_editor_completed_tab_tracks_technical_checks()
    {
        $editor = $this->createAdmin(['is_admin' => 1]);
        $this->assignRole($editor, 'technicalEditor');

        $application = $this->makeApplication('technical checks approved');
        $this->addTimeline($application, 'technical checks approved', [
            'assigned_by' => $editor->id,
        ]);

        $this->assertStringContainsString($application->application_id, $this->indexHtml($editor, 'completed'));
        $this->assertStringNotContainsString($application->application_id, $this->indexHtml($editor, 'closed'));
    }

    public function test_assistant_editor_completed_tab_tracks_final_decision()
    {
        $assistant = $this->createAdmin();
        $this->assignRole($assistant, 'assitantEditor');

        $application = $this->makeApplication('pre-accepted');
        $this->addTimeline($application, 'pre-accepted', [
            'assigned_by' => $assistant->id,
        ]);

        $this->assertStringContainsString($application->application_id, $this->indexHtml($assistant, 'completed'));
    }

    public function test_superadmin_completed_tab_tracks_chief_response()
    {
        $admin = $this->createAdmin(['is_admin' => 1]);
        $this->assignRole($admin, 'superadmin');

        $application = $this->makeApplication('chief-response');
        $this->addTimeline($application, 'chief-response', [
            'assigned_by' => $admin->id,
        ]);

        $this->assertStringContainsString($application->application_id, $this->indexHtml($admin, 'completed'));
    }

    public function test_publisher_completed_tab_tracks_typesetting()
    {
        $publisher = $this->createAdmin();
        $this->assignRole($publisher, 'Publisher');

        $technicalEditor = $this->createAdmin();

        $application = $this->makeApplication('typesetting review', [
            'assigned_to_id' => $technicalEditor->id,
        ]);
        $this->addTimeline($application, 'typesetting review', [
            'assigned_to_id' => $publisher->id,
        ]);

        $this->assertStringContainsString($application->application_id, $this->indexHtml($publisher, 'completed'));
        $this->assertStringNotContainsString($application->application_id, $this->indexHtml($publisher, 'closed'));
    }

    public function test_publisher_active_only_contains_manuscripts_assigned_to_them()
    {
        $publisher = $this->createAdmin();
        $this->assignRole($publisher, 'Publisher');

        $technicalEditor = $this->createAdmin();

        $currentlyAssigned = $this->makeApplication('accepted for publication', [
            'assigned_to_id' => $publisher->id,
        ]);
        // The publisher already finished typesetting, but this manuscript is
        // back in their queue for publication, so it must not stay in Completed.
        $this->addTimeline($currentlyAssigned, 'typesetting review', [
            'assigned_to_id' => $publisher->id,
        ]);

        $movedOn = $this->makeApplication('typesetting review', [
            'assigned_to_id' => $technicalEditor->id,
        ]);
        // Timeline history alone must not keep this in the publisher's Active list.
        $this->addTimeline($movedOn, 'typesetting review', [
            'assigned_to_id' => $publisher->id,
        ]);

        $activeHtml = $this->indexHtml($publisher);
        $completedHtml = $this->indexHtml($publisher, 'completed');

        $this->assertStringContainsString($currentlyAssigned->application_id, $activeHtml);
        $this->assertStringNotContainsString($currentlyAssigned->application_id, $completedHtml);

        $this->assertStringNotContainsString($movedOn->application_id, $activeHtml);
        $this->assertStringContainsString($movedOn->application_id, $completedHtml);
    }

    public function test_dual_role_staff_member_stays_active_until_their_editor_decision_is_recorded()
    {
        $staff = $this->createAdmin(['rev_type' => 'internal']);
        $this->assignRole($staff, 'Editor');
        $this->assignRole($staff, 'Reviewer');

        $application = $this->makeApplication('under review', [
            'assigned_to_id' => $staff->id,
        ]);
        $this->addTimeline($application, 'processing', [
            'assigned_to_id' => $staff->id,
        ]);
        $this->addTimeline($application, 'internal-reviewer-reviewed', [
            'assigned_to_id' => $staff->id,
            'assigned_by' => $staff->id,
        ]);

        $this->assertStringContainsString($application->application_id, $this->indexHtml($staff));
        $this->assertStringNotContainsString($application->application_id, $this->indexHtml($staff, 'completed'));

        $this->addTimeline($application, 'editor-accepts', [
            'assigned_to_id' => $staff->id,
            'assigned_by' => $staff->id,
        ]);

        $this->assertStringNotContainsString($application->application_id, $this->indexHtml($staff));
        $this->assertStringContainsString($application->application_id, $this->indexHtml($staff, 'completed'));
    }

    public function test_admin_my_manuscripts_contains_their_own_queue_only()
    {
        $admin = $this->createAdmin(['is_admin' => 1]);
        $this->assignRole($admin, 'superadmin');

        $chief = $this->makeApplication('chief-review', [
            'assigned_to_id' => $admin->id,
            'data' => ['status' => 'sended to chief approval'],
        ]);

        $otherEditor = $this->createAdmin();
        $this->assignRole($otherEditor, 'Editor');

        $someoneElse = $this->makeApplication('processing', [
            'assigned_to_id' => $otherEditor->id,
        ]);
        $this->addTimeline($someoneElse, 'processing', [
            'assigned_to_id' => $otherEditor->id,
        ]);

        $ownNonApproval = $this->makeApplication('processing', [
            'assigned_to_id' => $admin->id,
        ]);
        $this->addTimeline($ownNonApproval, 'processing', [
            'assigned_to_id' => $admin->id,
        ]);

        $html = $this->indexHtml($admin, 'mine');

        $this->assertStringContainsString($chief->application_id, $html);
        $this->assertStringNotContainsString($someoneElse->application_id, $html);
        $this->assertStringNotContainsString($ownNonApproval->application_id, $html);
    }

    public function test_admin_my_manuscripts_only_contains_manuscripts_assigned_to_them()
    {
        $admin = $this->createAdmin(['is_admin' => 1]);
        $this->assignRole($admin, 'technicalEditor');

        $assigned = $this->makeApplication('under review', [
            'assigned_to_id' => $admin->id,
        ]);

        $otherEditor = $this->createAdmin();
        $this->assignRole($otherEditor, 'Editor');

        $assignedToSomeoneElse = $this->makeApplication('under review', [
            'assigned_to_id' => $otherEditor->id,
        ]);

        $externalOnly = $this->makeApplication('under review', [
            'assigned_to_id' => $otherEditor->id,
            'assigned_to_ex_id' => $admin->id,
        ]);

        // This would have appeared through pendingFor(), but no assigned_to_id
        // points at the admin, so My Manuscripts must not show it.
        $techQueueOnly = $this->makeApplication('submitted');

        $html = $this->indexHtml($admin, 'mine');

        $this->assertStringContainsString($assigned->application_id, $html);
        $this->assertStringNotContainsString($assignedToSomeoneElse->application_id, $html);
        $this->assertStringNotContainsString($externalOnly->application_id, $html);
        $this->assertStringNotContainsString($techQueueOnly->application_id, $html);
    }

    public function test_assistant_editor_can_open_my_manuscripts()
    {
        $assistant = $this->createAdmin();
        $this->assignRole($assistant, 'assitantEditor');

        $application = $this->makeApplication('editor-revise', [
            'assigned_to_id' => $assistant->id,
        ]);

        $response = $this->actingAs($assistant, 'admin')->get(
            route('admin.application.index', ['type' => 'mine', 'perPage' => 500]),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $this->assertStringContainsString($application->application_id, $response->json('data'));
    }

    public function test_editor_and_reviewer_sidebar_uses_completed_and_closed_tabs()
    {
        $reviewer = $this->createAdmin(['rev_type' => 'internal']);
        $this->assignRole($reviewer, 'Reviewer');

        $response = $this->actingAs($reviewer, 'admin')->get(route('admin.application.index'));

        $response->assertOk()
            ->assertSee('Completed')
            ->assertSee('Closed')
            ->assertDontSee('Published');
    }

    public function test_admin_sidebar_uses_active_and_closed_with_their_filters()
    {
        $admin = $this->createAdmin(['is_admin' => 1]);
        $this->assignRole($admin, 'superadmin');

        $activeResponse = $this->actingAs($admin, 'admin')->get(route('admin.application.index'));

        $activeResponse->assertOk()
            ->assertSee('Active')
            ->assertSee('Closed')
            ->assertSee('All')
            ->assertSee('My')
            ->assertSee('Inactive and Deadlines')
            ->assertSee('Inactive')
            ->assertSee('Review Deadlines')
            ->assertSee('Assigned to')
            ->assertDontSee('My Manuscripts')
            ->assertDontSee('Completed');

        $closedResponse = $this->actingAs($admin, 'admin')->get(
            route('admin.application.index', ['type' => 'closed'])
        );

        $closedResponse->assertOk()
            ->assertSee('Published')
            ->assertSee('Rejected')
            ->assertSee('Withdrawn')
            ->assertSee('Duplicate')
            ->assertDontSee('Assigned to');
    }

    public function test_technical_editor_cannot_see_inactive_or_review_deadline_tools()
    {
        $technicalEditor = $this->createAdmin(['is_admin' => 1]);
        $this->assignRole($technicalEditor, 'technicalEditor');

        $application = $this->makeApplication('processing', [
            'assigned_to_id' => $technicalEditor->id,
        ]);
        $this->addTimeline($application, 'processing', [
            'assigned_to_id' => $technicalEditor->id,
        ]);

        $this->actingAs($technicalEditor, 'admin')
            ->get(route('admin.application.index'))
            ->assertOk()
            ->assertDontSee('Inactive and Deadlines')
            ->assertDontSee('Inactive')
            ->assertDontSee('Review Deadlines');

        $this->actingAs($technicalEditor, 'admin')
            ->get(route('admin.application.index', ['type' => 'inactive']))
            ->assertForbidden();

        $this->actingAs($technicalEditor, 'admin')
            ->get(route('admin.application.index', ['type' => 'review-deadline']))
            ->assertForbidden();
    }
}
