<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetAttemptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $otp;
    public $expiresAt;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $otp, $expiresAt)
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Password Reset Request - Hijrah App')
            ->markdown('emails.password-reset-attempt')
            ->with([
                'user' => $this->user,
                'otp' => $this->otp,
                'expiresAt' => $this->expiresAt,
            ]);
    }
} 