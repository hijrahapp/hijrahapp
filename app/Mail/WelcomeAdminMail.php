<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $locale = app()->getLocale() ?? 'en';

        return $this->locale($locale)
            ->from(config('mail.from.address'), __('mail.app-name'))
            ->subject(__('mail.welcome-subject'))
            ->markdown("emails.$locale.welcome-admin")
            ->with([
                'user' => $this->user,
                'password' => $this->password,
                'role' => $this->user->role->name->value
            ]);
    }
}

