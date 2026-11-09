<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Author;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationModuleTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(array $attributes = []): Admin
    {
        $suffix = uniqid();

        return Admin::create(array_merge([
            'username' => 'notification.admin.' . $suffix,
            'name' => 'Notification Admin',
            'email' => 'notification.admin.' . $suffix . '@example.test',
            'phone' => '5' . substr((string) time(), -6) . random_int(100, 999),
            'password' => Hash::make('secret123'),
            'status' => true,
            'is_admin' => 1,
        ], $attributes));
    }

    private function createAuthor(array $attributes = []): Author
    {
        $suffix = uniqid();

        return Author::create(array_merge([
            'username' => 'notification.author.' . $suffix,
            'name' => 'Notification',
            'lname' => 'Author',
            'email' => 'notification.author.' . $suffix . '@example.test',
            'phone' => '4' . substr((string) time(), -6) . random_int(100, 999),
            'password' => Hash::make('secret123'),
            'status' => true,
        ], $attributes));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'category' => 'application',
            'type' => 'application.test',
            'title' => 'Test notification',
            'message' => 'This is a test notification.',
            'url' => null,
            'icon' => 'ri-file-paper-2-line',
            'severity' => 'info',
        ], $overrides);
    }

    public function test_notification_service_creates_notifications_for_active_admins_and_authors()
    {
        $admin = $this->createAdmin();
        $author = $this->createAuthor();

        $service = app(NotificationService::class);

        $adminNotification = $service->send($admin, $this->payload(['title' => 'Admin notification']));
        $authorNotification = $service->send($author, $this->payload(['title' => 'Author notification']));

        $this->assertNotNull($adminNotification);
        $this->assertNotNull($authorNotification);

        $this->assertDatabaseHas('notifications', [
            'id' => $adminNotification->id,
            'notifiable_type' => Admin::class,
            'notifiable_id' => $admin->id,
            'category' => 'application',
        ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $authorNotification->id,
            'notifiable_type' => Author::class,
            'notifiable_id' => $author->id,
            'category' => 'application',
        ]);

        $this->assertSame('Admin notification', $adminNotification->data['title']);
        $this->assertSame('This is a test notification.', $authorNotification->data['message']);
    }

    public function test_notification_service_skips_inactive_users()
    {
        $admin = $this->createAdmin(['status' => false]);
        $author = $this->createAuthor(['status' => false]);

        $service = app(NotificationService::class);

        $this->assertNull($service->send($admin, $this->payload()));
        $this->assertNull($service->send($author, $this->payload()));

        $this->assertDatabaseMissing('notifications', [
            'notifiable_type' => Admin::class,
            'notifiable_id' => $admin->id,
        ]);

        $this->assertDatabaseMissing('notifications', [
            'notifiable_type' => Author::class,
            'notifiable_id' => $author->id,
        ]);
    }

    public function test_admin_can_list_and_manage_only_their_own_notifications()
    {
        $admin = $this->createAdmin();
        $other = $this->createAdmin();

        $service = app(NotificationService::class);
        $first = $service->send($admin, $this->payload(['title' => 'First']));
        $second = $service->send($admin, $this->payload(['title' => 'Second']));
        $foreign = $service->send($other, $this->payload(['title' => 'Foreign']));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('First')
            ->assertSee('Second')
            ->assertDontSee('Foreign');

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.notifications.latest'))
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertSee('First');

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.notifications.unread-count'))
            ->assertOk()
            ->assertJsonPath('count', 2);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.notifications.read', $first->id))
            ->assertOk()
            ->assertJsonPath('count', 1);

        $this->assertNotNull($first->fresh()->read_at);
        $this->assertNull($second->fresh()->read_at);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.notifications.read-all'))
            ->assertOk()
            ->assertJsonPath('count', 0);

        $this->assertNotNull($second->fresh()->read_at);

        $this->actingAs($admin, 'admin')
            ->deleteJson(route('admin.notifications.destroy', $second->id))
            ->assertOk();

        $this->assertDatabaseMissing('notifications', ['id' => $second->id]);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.notifications.read', $foreign->id))
            ->assertNotFound();

        $this->assertNull($foreign->fresh()->read_at);
    }

    public function test_author_can_open_a_notification_and_is_redirected_to_its_url()
    {
        $author = $this->createAuthor();

        $notification = app(NotificationService::class)->send($author, $this->payload([
            'title' => 'Author notification',
            'url' => route('author.dashboard'),
        ]));

        $this->actingAs($author, 'web')
            ->get(route('author.notifications.open', $notification->id))
            ->assertRedirect(route('author.dashboard'));

        $this->assertNotNull($notification->fresh()->read_at);

        $this->actingAs($author, 'web')
            ->get(route('author.notifications.index'))
            ->assertOk()
            ->assertSee('Author notification');
    }

    public function test_manual_email_creates_an_in_app_notification_for_the_recipient()
    {
        Mail::fake();

        $sender = $this->createAdmin();
        $recipient = $this->createAdmin(['name' => 'Email Recipient']);

        $response = $this->actingAs($sender, 'admin')->postJson(route('admin.sendMail'), [
            'subject' => 'A direct message',
            'message' => 'Please review the attached manuscript.',
            'recipients' => [
                [
                    'recipient_type' => Admin::class,
                    'recipient_id' => $recipient->id,
                ],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => Admin::class,
            'notifiable_id' => $recipient->id,
            'category' => 'message',
            'type' => 'message.received',
        ]);

        $notification = $recipient->notifications()->first();
        $this->assertSame('A direct message', $notification->data['title']);
        $this->assertSame('Please review the attached manuscript.', $notification->data['message']);
    }

    public function test_prune_command_removes_notifications_older_than_ninety_days()
    {
        $admin = $this->createAdmin();
        $service = app(NotificationService::class);

        $old = $service->send($admin, $this->payload(['title' => 'Old']));
        $recent = $service->send($admin, $this->payload(['title' => 'Recent']));

        $old->forceFill([
            'created_at' => now()->subDays(91),
            'updated_at' => now()->subDays(91),
        ])->save();

        $this->artisan('notifications:prune')
            ->expectsOutput('Pruned 1 notification(s) older than 90 days.')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('notifications', ['id' => $old->id]);
        $this->assertDatabaseHas('notifications', ['id' => $recent->id]);
    }
}
