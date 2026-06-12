<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_token_and_verification_email(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/register', [
            'name' => 'Creator One',
            'email' => 'creator@example.com',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['message', 'data' => ['user' => ['id', 'name', 'email', 'role'], 'token']])
            ->assertJsonPath('data.user.email', 'creator@example.com')
            ->assertJsonPath('data.user.role', 'creator')
            ->assertJsonPath('data.user.email_verified', false);

        $user = User::where('email', 'creator@example.com')->firstOrFail();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'creator@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Creator Two',
            'email' => 'creator@example.com',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Creator One',
            'email' => 'creator@example.com',
            'password' => 'secure-password-123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_registration_response_never_exposes_password(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Creator One',
            'email' => 'creator@example.com',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ]);

        $response->assertCreated();
        $this->assertStringNotContainsString('password', json_encode($response->json('data.user')));
    }
}
