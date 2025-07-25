<x-mail::layout>
<x-slot:header>
<x-mail::header :url="''">
{{ __('mail.app-name') }}
</x-mail::header>
</x-slot:header>

Hi {{ $user->name ?? $user->email }},

We’re so glad you joined Hijrah. This is a place where you can pause, reflect, and take steps to improve your life.

## Here’s what you can do next:
- Learn more about yourself
- Explore where things feel balanced or need a little support
- Get helpful programs and tips made just for you

If you have any questions or need help, we’re always here for you.

Thank you,<br>
{{ __('mail.signature') }}

<x-slot:footer>
<x-mail::footer>
© 2025 {{ config('app.name') }}. All rights reserved.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout> 