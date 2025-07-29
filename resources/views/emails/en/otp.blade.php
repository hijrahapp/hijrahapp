<x-mail::layout>
<x-slot:header>
<x-mail::header :url="''">
{{ __('mail.app-name') }}
</x-mail::header>
</x-slot:header>

Hello {{ $user->name ?? $user->email }},

Your One-Time Password (OTP) is: # **{{ $otp }}**

If you did not request this code, please ignore this email.

Thanks,<br>
{{ __('mail.signature') }}

<x-slot:footer>
<x-mail::footer>
© 2025 {{ config('app.name') }}. All rights reserved.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
