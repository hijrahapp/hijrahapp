<x-mail::layout>
<x-slot:header>
<x-mail::header :url="''">
{{ __('mail.app-name') }}
</x-mail::header>
</x-slot:header>

Hi {{ $user->name ?? $user->email }},

Welcome to the Hijrah Admin Panel!

You've been granted access as an {{ $role }}, and we're excited to have you as part of the team.

Here are your login details:<br>
-	Panel Link: {{ config('app.url') }}<br>
-	Username (Email): {{ $user->email }}<br>
-	Temporary Password: {{ $password }}

🔒 Important:<br>
This is a temporary password. For your security, please change it right after logging in.

If you have any questions or need support, feel free to reach out.

Welcome aboard!<br>
{{ __('mail.admin-signature') }}

<x-slot:footer>
<x-mail::footer>
© 2025 {{ config('app.name') }}. All rights reserved.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
