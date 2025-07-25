<x-mail::layout>
<x-slot:header>
<x-mail::header :url="''">
{{ __('mail.app-name') }}
</x-mail::header>
</x-slot:header>

<div dir="rtl">
مرحبًا {{ $user->name ?? $user->email }}،<br>
<br>
رمز الدخول لمرة واحدة الخاص بك هو<br>
<br>
<b>{{ $otp }}</b><br>
<br>
سينتهي صلاحية هذا الرمز في ({{ $expiresAt->format('H:i, d M Y') }}).<br> 
<br>
إذا لم تطلب هذا الرمز، يرجى تجاهل هذا البريد الإلكتروني.<br>
<br>
شكرًا،<br>
{{ __('mail.signature') }}
</div>

<x-slot:footer>
<x-mail::footer>
© 2025 {{ config('app.name') }}. All rights reserved.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
