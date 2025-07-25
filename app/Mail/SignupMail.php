<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SignupMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($otp, $user)
    {
        $this->otp = $otp;
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
            ->subject(__('mail.signup-subject'))
            ->markdown("emails.$locale.signup")
            ->with([
                'otp' => $this->otp,
                'user' => $this->user,
            ]);
    }
}
