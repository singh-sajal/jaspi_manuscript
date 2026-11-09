<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationTimeline;
use App\Models\Attachment;
use App\Models\Author;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthorProofreadingStepTest extends TestCase
{
    use DatabaseTransactions;

    private function createAuthor(): Author
    {
        $suffix = uniqid();

        return Author::create([
            'username' => 'proofreading.step.' . $suffix,
            'name' => 'Proofreading',
            'lname' => 'Author',
            'email' => 'proofreading.step.' . $suffix . '@example.test',
            'phone' => '9' . substr((string) time(), -6) . random_int(100, 999),
            'password' => Hash::make('secret123'),
            'status' => true,
        ]);
    }

    private function makeProofreadingApplication(Author $author, string $status = 'proofreading'): Application
    {
        $suffix = uniqid();

        return Application::create([
            'application_id' => 'PROOF-' . $suffix,
            'title' => 'Proofreading step test ' . $suffix,
            'submission_type' => 'new_submission',
            'status' => $status,
            'author_id' => $author->id,
        ]);
    }

    public function test_proofreading_action_and_relevant_files_appear_at_the_top_of_the_author_page()
    {
        $author = $this->createAuthor();
        $application = $this->makeProofreadingApplication($author);

        Attachment::create([
            'application_id' => $application->id,
            'file_name' => 'manuscript.docx',
            'file_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'file_url' => 'uploads/applications/manuscript.docx',
            'attachment_type' => 'Upload Manuscript',
        ]);

        ApplicationTimeline::create([
            'application_id' => $application->id,
            'status' => 'proofreading',
            'is_visible_to_author' => 1,
            'remark' => 'Author(Owner of Manuscript) required to proofread the manuscript.',
            'data' => [
                'comment' => '<p>Proofread this paragraph carefully.</p>',
            ],
        ]);

        $response = $this->actingAs($author, 'web')
            ->get(route('author.application.show', $application->uuid));

        $response->assertOk()
            ->assertSee('Current Step: Proofreading')
            ->assertSee('Upload corrected proof')
            ->assertSee('Proofread this paragraph carefully.')
            ->assertSee('View manuscript')
            ->assertSee('View all attachments')
            ->assertSeeInOrder([
                'Current Step: Proofreading',
                'Co-Authors',
                'Attachments',
            ]);
    }

    public function test_proofreading_action_is_not_shown_for_other_statuses()
    {
        $author = $this->createAuthor();
        $application = $this->makeProofreadingApplication($author, 'revise');

        $response = $this->actingAs($author, 'web')
            ->get(route('author.application.show', $application->uuid));

        $response->assertOk()
            ->assertDontSee('Current Step: Proofreading')
            ->assertDontSee('Upload corrected proof');
    }
}
