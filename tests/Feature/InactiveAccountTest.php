<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Author;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InactiveAccountTest extends TestCase
{
    use DatabaseTransactions;

    private function createAuthor(bool $status): Author
    {
        $suffix = uniqid();

        return Author::create([
            'username' => 'inactive.author.' . $suffix,
            'name' => 'Inactive',
            'lname' => 'Author',
            'email' => 'inactive.author.' . $suffix . '@example.test',
            'phone' => '8' . substr((string) time(), -6) . random_int(100, 999),
            'password' => Hash::make('secret123'),
            'status' => $status,
        ]);
    }

    private function createAdmin(bool $status): Admin
    {
        $suffix = uniqid();

        return Admin::create([
            'username' => 'inactive.admin.' . $suffix,
            'name' => 'Inactive Admin',
            'email' => 'inactive.admin.' . $suffix . '@example.test',
            'phone' => '7' . substr((string) time(), -6) . random_int(100, 999),
            'password' => Hash::make('secret123'),
            'status' => $status,
        ]);
    }

    public function test_inactive_author_is_logged_out_by_middleware()
    {
        $author = $this->createAuthor(false);

        $response = $this->actingAs($author, 'web')->get(route('author.dashboard'));

        $response->assertRedirect(route('author.login'));
        $this->assertGuest('web');
    }

    public function test_inactive_admin_is_logged_out_by_middleware()
    {
        $admin = $this->createAdmin(false);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_inactive_author_cannot_login()
    {
        $author = $this->createAuthor(false);

        $response = $this->post(route('author.login'), [
            'username' => $author->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'status' => '500',
            'msg' => 'Login not Permitted ! Contact Administrator',
        ]);
        $this->assertGuest('web');
    }

    public function test_active_author_can_still_login()
    {
        $author = $this->createAuthor(true);

        $response = $this->post(route('author.login'), [
            'username' => $author->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => '200']);
        $this->assertAuthenticatedAs($author, 'web');
    }
}
