<x-mail::layout>
<x-slot:header>
<x-mail::header :url="''">
{{ __('mail.app-name') }}
</x-mail::header>
</x-slot:header>

Hello {{ $user->name ?? $user->email }},

Thank you for registering with {{ __('mail.app-name') }}!

To verify your email address, please enter the following code in the app.

Your Verification Code:
# **{{ $otp }}**

If you didn't request this, you can safely ignore this email.

Thank you,<br>
{{ __('mail.signature') }}

<x-slot:footer>
<x-mail::footer>
© 2025 {{ config('app.name') }}. All rights reserved.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
