<x-mail::layout>
<x-slot:header>
<x-mail::header :url="''">
{{ __('mail.app-name') }}
</x-mail::header>
</x-slot:header>

<div dir="rtl">
مرحبًا {{ $user->name ?? $user->email }}،<br>
<br>
شكرًا لتسجيلك في تطبيق هِجرة!<br>
لإتمام عملية التسجيل، يرجى إدخال رمز التحقق التالي في التطبيق.<br>
<br>
رمز التحقق الخاص بك: <b>{{ $otp }}</b><br>
<br>
إذا لم تطلب هذا الرمز، يمكنك تجاهل هذه الرسالة بأمان<br>
<br>
شكرًا لك،<br>
{{ __('mail.signature') }}
</div>

<x-slot:footer>
<x-mail::footer>
© 2025 {{ config('app.name') }}. All rights reserved.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
