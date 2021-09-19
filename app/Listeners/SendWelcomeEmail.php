<?php

namespace App\Listeners;

use App\Events\Registered;
use App\Mail\Welcome;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeEmail
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
     * @param  Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        if ($event instanceof Registered) {
            // TODO: replace with a deep link to open the app
            $button_url = '#welcome';
            Mail::to($event->user->email)->send(new Welcome($event->user, $button_url));
        }
    }
}
