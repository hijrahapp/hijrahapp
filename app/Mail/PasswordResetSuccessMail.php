<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $locale = app()->getLocale() ?? 'en';

        return $this->locale($locale)
            ->from(config('mail.from.address'), __('mail.app-name'))
            ->subject(__('mail.password-reset-success-subject'))
            ->markdown("emails.$locale.password-reset-success")
            ->with([
                'user' => $this->user,
            ]);
    }
}
