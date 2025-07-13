<x-mail::message>
# Your One-Time Password (OTP)

Hello {{ $user->name ?? $user->email }},

Your One-Time Password (OTP) is:

# **{{ $otp }}**

This code will expire at {{ $expiresAt->format('H:i, d M Y') }} (in about 15 minutes).

If you did not request this code, please ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
