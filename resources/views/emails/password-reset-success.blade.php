<x-mail::message>
# Password Reset Successful

Hello {{ $user->name ?? $user->email }},

Your password has been successfully reset for your Hijrah App account.

## Security Confirmation

Your account password was changed at {{ now()->format('H:i, d M Y') }}.

## Important Security Tips

- Use a strong, unique password
- Enable two-factor authentication if available
- Never share your login credentials
- Log out from all devices if you suspect unauthorized access

If you did not perform this password reset, please contact our support team immediately as your account may have been compromised.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
