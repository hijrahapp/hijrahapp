<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $user;
    public $expiresAt;

    /**
     * Create a new message instance.
     */
    public function __construct($otp, $user, $expiresAt)
    {
        $this->otp = $otp;
        $this->user = $user;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $locale = app()->getLocale() ?? 'en';

        return $this->locale($locale)
            ->from(config('mail.from.address'), __('mail.app-name'))
            ->subject(__('mail.otp-subject'))
            ->markdown("emails.$locale.otp")
            ->with([
                'otp' => $this->otp,
                'user' => $this->user,
                'expiresAt' => $this->expiresAt,
            ]);
    }
}
