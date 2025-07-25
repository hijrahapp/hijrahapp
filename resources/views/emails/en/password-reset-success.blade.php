<x-mail::layout>
<x-slot:header>
<x-mail::header :url="''">
{{ __('mail.app-name') }}
</x-mail::header>
</x-slot:header>

Hello {{ $user->name ?? $user->email }},

Your password has been successfully reset for your account.

## Security Confirmation

Your account password was changed at {{ now()->format('H:i, d M Y') }}.

If you did not perform this password reset, please contact our support team immediately as your account may have been compromised.

Thanks,<br>
{{ __('mail.signature') }}

<x-slot:footer>
<x-mail::footer>
© 2025 {{ config('app.name') }}. All rights reserved.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
