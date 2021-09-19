<?php

namespace Tests\Feature;

use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\URL;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuthRoutesTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test Auth routes.
     *
     * @return void
     */
    public function test_post_register()
    {
        Passport::actingAsClient(
            Client::factory()->create()
        );

        $testUser = User::factory()->make();

        $response = $this->postJson(route('register'), [
            'email' => $testUser->email,
            'password' => $testUser->password,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.attributes.email', $testUser->email);
        $response->assertJsonPath('data.attributes.id', 1);
    }

    public function test_get_user()
    {
        $testUser = User::factory()->create();

        Passport::actingAs(
            $testUser
        );

        $response = $this->getJson(route('user'));

        $response->assertOk();
        $response->assertJsonPath('data.attributes.email', $testUser->email);
    }
    
    public function test_post_logout()
    {
        Passport::actingAs(
            User::factory()->create()
        );

        $response = $this->postJson(route('logout'));

        $response->assertOk();
        $response->assertJsonPath('message', __('auth.logout_success'));
    }

    public function test_post_forgot()
    {
        $testUser = User::factory()->create();

        $response = $this->postJson(route('forgot'), [
            'email' => $testUser->email
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
    }

    public function test_post_reset_password()
    {
        $testUser = User::factory()->create();

        $this->post(route('forgot'), [
            'email' => $testUser->email
        ]);

        $token = PasswordReset::where('email', $testUser->email)->first()->token;

        $response = $this->postJson(route('reset-password'), [
            'password' => $testUser->password,
            'token' => $token
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
    }

    public function test_get_verification_verify()
    {
        $testUser = User::factory()->create();

        $userId = User::where('email', $testUser->email)
            ->first()
            ->id;

        $signedURL = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addHours(config('mail.verification.expires_in_hours')),
            ['id' => $userId]
        );

        $response = $this->get($signedURL);

        $response->assertRedirect(env('EMAIL_VERIFY_SUCCESS_URL'));
    }

    public function test_post_verification_resend()
    {
        $user = User::factory()->unverified()->create();

        Passport::actingAs(
            $user
        );

        $response = $this->postJson(route('verification.resend', [
            'id' => $user->id
        ]));

        $response->assertOk();
        $response->assertJsonPath('data.message', __('auth.resend_verify_email_success'));
    }

    public function test_post_verification_resend_denied()
    {
        $user = User::factory()->create();

        Passport::actingAs(
            $user
        );

        $response = $this->postJson(route('verification.resend', [
            'id' => $user->id
        ]));

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
            'errors' => [
                'message' => [ __('auth.resend_verify_email_denied') ]
            ]
        ]);
    }
}
