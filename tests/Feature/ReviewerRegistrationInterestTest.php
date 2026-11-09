<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Integration;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReviewerRegistrationInterestTest extends TestCase
{
    use DatabaseTransactions;

    private function otpSession(string $email, string $phone): \stdClass
    {
        $data = new \stdClass();
        $data->email = $email;
        $data->phone = $phone;
        $data->is_otp_verify = 1;

        return $data;
    }

    private function payload(string $email, string $phone, array $extra = []): array
    {
        return array_merge([
            'role_name' => 'Reviewer',
            'name' => 'Test Reviewer',
            'email' => $email,
            'phone' => $phone,
            'dob' => '1990-01-01',
            'password' => 'secret123',
            'terms' => '1',
            'gender' => 'Other',
            'saspi_member' => 'No',
            'designation' => 'Associate Professor',
            'affiliation' => 'AIIMS, Dept. of Medicine',
            'research_experience_years' => 5,
            'avg_articles_published' => 3,
            'h_index' => '12',
            'country' => 'India',
            'state' => 'Delhi',
            'city' => 'New Delhi',
            'address' => 'Street, area, landmark',
            'pincode' => '110001',
        ], $extra);
    }

    private function uniqueEmail(): string
    {
        return 'reviewer.test.' . uniqid() . '@example.test';
    }

    private function uniquePhone(): string
    {
        return '9' . substr((string) time(), -6) . substr((string) random_int(1000, 9999), 0, 3);
    }

    public function test_registration_form_lists_integrations()
    {
        $email = $this->uniqueEmail();
        $phone = $this->uniquePhone();

        $response = $this->withSession([
            'otp_data' => $this->otpSession($email, $phone),
        ])->get(route('admin.registration'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.auth.reviewer-signup');
        $response->assertViewHas('integrations');
        $response->assertSee('name="integrations[]"', false);

        $integrations = Integration::orderBy('name')->get();
        $this->assertGreaterThan(0, $integrations->count());

        foreach ($integrations as $integration) {
            $response->assertSee('value="' . $integration->id . '"', false);
            $response->assertSee(e($integration->name), false);
        }
    }

    public function test_registration_persists_selected_integrations()
    {
        $email = $this->uniqueEmail();
        $phone = $this->uniquePhone();
        $ids = Integration::orderBy('name')->limit(2)->pluck('id')->all();

        $response = $this->withSession([
            'otp_data' => $this->otpSession($email, $phone),
        ])->post(route('admin.registration'), $this->payload($email, $phone, [
            'integrations' => $ids,
        ]));

        $response->assertStatus(200);
        $response->assertJson(['status' => '200']);

        $admin = Admin::where('email', $email)->first();
        $this->assertNotNull($admin, 'Reviewer was not created.');
        $this->assertEqualsCanonicalizing(
            $ids,
            $admin->integrations()->pluck('integrations.id')->all()
        );

        $this->assertEqualsCanonicalizing(
            ['Reviewer'],
            $admin->getRoleNames()->all()
        );
    }

    public function test_registration_requires_at_least_one_integration()
    {
        $email = $this->uniqueEmail();
        $phone = $this->uniquePhone();

        $response = $this->withSession([
            'otp_data' => $this->otpSession($email, $phone),
        ])->post(route('admin.registration'), $this->payload($email, $phone));

        $response->assertStatus(400);
        $response->assertJsonValidationErrors('integrations');
        $this->assertNull(Admin::where('email', $email)->first());
    }

    public function test_registration_rejects_unknown_integration_id()
    {
        $email = $this->uniqueEmail();
        $phone = $this->uniquePhone();

        $response = $this->withSession([
            'otp_data' => $this->otpSession($email, $phone),
        ])->post(route('admin.registration'), $this->payload($email, $phone, [
            'integrations' => [999999],
        ]));

        $response->assertStatus(400);
        $response->assertJsonValidationErrors('integrations.0');
        $this->assertNull(Admin::where('email', $email)->first());
    }

    public function test_registration_deduplicates_repeated_integration_ids()
    {
        $email = $this->uniqueEmail();
        $phone = $this->uniquePhone();
        $id = (int) Integration::orderBy('name')->value('id');

        $response = $this->withSession([
            'otp_data' => $this->otpSession($email, $phone),
        ])->post(route('admin.registration'), $this->payload($email, $phone, [
            'integrations' => [$id, (string) $id, $id],
        ]));

        $response->assertStatus(200);

        $admin = Admin::where('email', $email)->first();
        $this->assertNotNull($admin);
        $this->assertSame([$id], $admin->integrations()->pluck('integrations.id')->all());
    }
}
