<?php

namespace Tests\Feature;

use App\Mail\ResetPassword;
use App\Mail\VerifyEmail;
use App\Mail\Welcome;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;


class MailTest extends TestCase
{
    /**
     * Test emails content
     *
     * @return void
     */
    public function test_verify_email()
    {
        $user = User::factory()->make();
        $button_url = 'verify_email_url';
        $mailable = new VerifyEmail($user, $button_url);

        $mailable->assertSeeInHtml($button_url);
        $mailable->assertSeeInHtml('hello@larabel-boilderplate.com');

        $mailable->assertSeeInText($button_url);
    }
    public function test_reset_password()
    {
        $user = User::factory()->make();
        $token = 'token';
        $button_url = $this->url = env('FRONT_URL') . '/reset-password/' . $token;
        $mailable = new ResetPassword($user, $token);

        $mailable->assertSeeInHtml($button_url);
        $mailable->assertSeeInHtml('hello@larabel-boilderplate.com');
        $mailable->assertSeeInHtml($user->email);

        $mailable->assertSeeInText($button_url);
        $mailable->assertSeeInText($user->email);
    }
    public function test_welcome()
    {
        $user = User::factory()->make();
        $button_url = '#welcome';
        $mailable = new Welcome($user, $button_url);

        $mailable->assertSeeInHtml($button_url);
        $mailable->assertSeeInText($button_url);
    }
}
