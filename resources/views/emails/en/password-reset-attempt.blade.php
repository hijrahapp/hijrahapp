<x-mail::layout>
<x-slot:header>
<x-mail::header :url="''">
{{ __('mail.app-name') }}
</x-mail::header>
</x-slot:header>

Hello {{ $user->name ?? $user->email }},

We received a request to reset your password.

To proceed, please enter the following code in the Hijrah app.

Your Reset Code: 
# **{{ $otp }}**

If you did not request this password reset, you can safely ignore this email.

Thanks,<br>
{{ __('mail.signature') }}

<x-slot:footer>
<x-mail::footer>
© 2025 {{ config('app.name') }}. All rights reserved.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout> 