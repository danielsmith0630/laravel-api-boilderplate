<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user instance.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * The password reset url.
     *
     * @var string
     */
    public $url;

        /**
     * The name of the theme that should be used when formatting the message.
     *
     * @var string|null
     */
    public $theme = 'light';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
        $this->url = env('FRONT_URL') . '/reset-password/' . $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.markdown.reset_password');
    }
}
