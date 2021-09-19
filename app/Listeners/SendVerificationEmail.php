<?php

namespace App\Listeners;

use App\Events\Registered;
use App\Events\Remind;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use App\Mail\VerifyEmail;

class SendVerificationEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Registered|Remind  $event
     * @return void
     */
    public function handle($event)
    {
        if ($event instanceof Registered || $event instanceof Remind) {
            $button_url = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addHours(config('mail.verification.expires_in_hours')),
                ['id' => $event->user->getKey()]
            );
    
            Mail::to($event->user->email)->send(new VerifyEmail($event->user, $button_url));
        }
    }
}
