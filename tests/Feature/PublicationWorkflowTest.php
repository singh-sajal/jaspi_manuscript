<?php

namespace Tests\Feature;

use App\Mail\PublicationAcceptedMail;
use App\Models\Admin;
use App\Models\Application;
use App\Models\ApplicationTimeline;
use App\Models\Author;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PublicationWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(array $attributes = []): Admin
    {
        $suffix = uniqid();

        return Admin::create(array_merge([
            'username' => 'publication.workflow.' . $suffix,
            'name' => 'Publication Workflow Test',
            'email' => 'publication.workflow.' . $suffix . '@example.test',
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

    private function createAuthor(array $attributes = []): Author
    {
        $suffix = uniqid();

        return Author::create(array_merge([
            'username' => 'publication.author.' . $suffix,
            'name' => 'Publication Author',
            'lname' => 'Test',
            'email' => 'publication.author.' . $suffix . '@example.test',
            'phone' => '3' . substr((string) time(), -6) . random_int(100, 999),
            'password' => Hash::make('secret123'),
            'status' => true,
        ], $attributes));
    }

    private function makeApplication(string $status, array $attributes = []): Application
    {
        $suffix = uniqid();

        return Application::create(array_merge([
            'application_id' => 'PUB-' . $suffix,
            'title' => 'Publication workflow test ' . $suffix,
            'submission_type' => 'new_submission',
            'status' => $status,
        ], $attributes));
    }

    private function assignPublisherTimeline(Application $application, Admin $publisher): ApplicationTimeline
    {
        return ApplicationTimeline::create([
            'application_id' => $application->id,
            'status' => 'accepted',
            'assigned_to_id' => $publisher->id,
            'is_visible_to_author' => 1,
            'remark' => 'Assigned to publisher.',
        ]);
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

    public function test_technical_editor_acceptance_returns_the_manuscript_to_the_publisher()
    {
        Mail::fake();

        $technicalEditor = $this->createAdmin();
        $this->assignRole($technicalEditor, 'technicalEditor');

        $publisher = $this->createAdmin(['name' => 'Assigned Publisher']);
        $this->assignRole($publisher, 'Publisher');

        $author = $this->createAuthor();

        $application = $this->makeApplication('author corrections submitted', [
            'author_id' => $author->id,
            'assigned_to_id' => $technicalEditor->id,
        ]);
        $this->assignPublisherTimeline($application, $publisher);

        $response = $this->actingAs($technicalEditor, 'admin')->post(
            route('admin.application.acceptForPublication', $application->uuid),
            ['comment' => 'The corrected proof is ready for publication.']
        );

        $response->assertOk()->assertJson([
            'status' => '200',
            'redirect' => route('admin.application.index'),
        ]);

        $application->refresh();

        $this->assertSame(Application::ACCEPTED_FOR_PUBLICATION_STATUS, $application->status);
        $this->assertSame($publisher->id, (int) $application->assigned_to_id);

        $this->assertDatabaseHas('application_timelines', [
            'application_id' => $application->id,
            'status' => Application::ACCEPTED_FOR_PUBLICATION_STATUS,
            'assigned_to_id' => $publisher->id,
            'assigned_by' => $technicalEditor->id,
            'is_visible_to_author' => 1,
        ]);

        Mail::assertQueued(PublicationAcceptedMail::class, function (PublicationAcceptedMail $mail) use ($publisher) {
            return $mail->hasTo($publisher->email);
        });
    }

    public function test_only_the_assigned_technical_editor_can_accept_for_publication()
    {
        $assignedTechnicalEditor = $this->createAdmin(['name' => 'Assigned Technical Editor']);
        $this->assignRole($assignedTechnicalEditor, 'technicalEditor');

        $otherTechnicalEditor = $this->createAdmin(['name' => 'Other Technical Editor']);
        $this->assignRole($otherTechnicalEditor, 'technicalEditor');

        $publisher = $this->createAdmin();
        $this->assignRole($publisher, 'Publisher');

        $application = $this->makeApplication('author corrections submitted', [
            'assigned_to_id' => $assignedTechnicalEditor->id,
        ]);
        $this->assignPublisherTimeline($application, $publisher);

        $this->actingAs($otherTechnicalEditor, 'admin')
            ->post(route('admin.application.acceptForPublication', $application->uuid))
            ->assertForbidden();

        $this->assertSame('author corrections submitted', $application->fresh()->status);
    }

    public function test_publisher_can_publish_after_technical_editor_acceptance()
    {
        Mail::fake();

        $publisher = $this->createAdmin(['name' => 'Publishing User']);
        $this->assignRole($publisher, 'Publisher');

        $author = $this->createAuthor();

        $application = $this->makeApplication(Application::ACCEPTED_FOR_PUBLICATION_STATUS, [
            'author_id' => $author->id,
            'assigned_to_id' => $publisher->id,
        ]);

        $response = $this->actingAs($publisher, 'admin')->post(
            route('admin.application.publish', $application->uuid),
            ['doi' => '10.1234/publication.workflow']
        );

        $response->assertOk()->assertJson(['status' => '200']);

        $application->refresh();

        $this->assertSame('published', $application->status);
        $this->assertSame('10.1234/publication.workflow', $application->published_doi);

        $this->assertDatabaseHas('application_timelines', [
            'application_id' => $application->id,
            'status' => 'published',
            'assigned_to_id' => $publisher->id,
        ]);
    }

    public function test_publisher_cannot_publish_before_technical_editor_acceptance()
    {
        $publisher = $this->createAdmin();
        $this->assignRole($publisher, 'Publisher');

        $application = $this->makeApplication('author corrections submitted', [
            'assigned_to_id' => $publisher->id,
        ]);

        $this->actingAs($publisher, 'admin')
            ->post(route('admin.application.publish', $application->uuid), [
                'doi' => '10.1234/not-ready',
            ])
            ->assertStatus(400);

        $this->assertSame('author corrections submitted', $application->fresh()->status);
    }

    public function test_publisher_sees_the_publish_action_only_at_the_accepted_for_publication_status()
    {
        $publisher = $this->createAdmin(['name' => 'Publishing User']);
        $this->assignRole($publisher, 'Publisher');

        $application = $this->makeApplication(Application::ACCEPTED_FOR_PUBLICATION_STATUS, [
            'assigned_to_id' => $publisher->id,
        ]);

        $this->actingAs($publisher, 'admin')
            ->get(route('admin.application.show', $application->uuid))
            ->assertOk()
            ->assertSee('Add DOI', false)
            ->assertDontSee('Accept for Publication');

        $this->assertStringContainsString(
            $application->application_id,
            $this->indexHtml($publisher)
        );
    }

    public function test_technical_editor_actions_are_visible_and_acceptance_leaves_the_active_queue()
    {
        $technicalEditor = $this->createAdmin();
        $this->assignRole($technicalEditor, 'technicalEditor');

        $publisher = $this->createAdmin();
        $this->assignRole($publisher, 'Publisher');

        $active = $this->makeApplication('author corrections submitted', [
            'assigned_to_id' => $technicalEditor->id,
        ]);
        $this->assignPublisherTimeline($active, $publisher);

        $accepted = $this->makeApplication(Application::ACCEPTED_FOR_PUBLICATION_STATUS, [
            'assigned_to_id' => $publisher->id,
        ]);
        $this->assignPublisherTimeline($accepted, $publisher);
        ApplicationTimeline::create([
            'application_id' => $accepted->id,
            'status' => Application::ACCEPTED_FOR_PUBLICATION_STATUS,
            'assigned_to_id' => $publisher->id,
            'assigned_by' => $technicalEditor->id,
            'is_visible_to_author' => 1,
        ]);

        $this->actingAs($technicalEditor, 'admin')
            ->get(route('admin.application.show', $active->uuid))
            ->assertOk()
            ->assertSee('Accept for Publication')
            ->assertDontSee('Accept & Publish');

        $activeHtml = $this->indexHtml($technicalEditor);
        $completedHtml = $this->indexHtml($technicalEditor, 'completed');

        $this->assertStringContainsString($active->application_id, $activeHtml);
        $this->assertStringNotContainsString($accepted->application_id, $activeHtml);
        $this->assertStringContainsString($accepted->application_id, $completedHtml);

        // The editor accepted this manuscript, so they keep read access from
        // their Completed list even though it is now assigned to the publisher.
        $this->actingAs($technicalEditor, 'admin')
            ->get(route('admin.application.show', $accepted->uuid))
            ->assertOk()
            ->assertSee($accepted->title);
    }
}
