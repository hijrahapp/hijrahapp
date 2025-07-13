<x-mail::message>
# Password Reset Request

Hello {{ $user->name ?? $user->email }},

We received a request to reset your password for your Hijrah App account.

## Your One-Time Password (OTP) is:

# **{{ $otp }}**

This code will expire at {{ $expiresAt->format('H:i, d M Y') }} (in about 15 minutes).

## Important Security Notice

- This OTP is only valid for 15 minutes
- Never share this code with anyone
- Our team will never ask for this code
- If you didn't request this password reset, please ignore this email and ensure your account is secure

If you did not request this password reset, please contact our support team immediately.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message> 