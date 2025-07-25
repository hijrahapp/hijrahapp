<x-mail::layout>
<x-slot:header>
<x-mail::header :url="''">
{{ __('mail.app-name') }}
</x-mail::header>
</x-slot:header>

<div dir="rtl">
مرحبًا {{ $user->name ?? $user->email }}،<br>
<br>
لقد تلقينا طلبًا لإعادة تعيين كلمة المرور الخاصة بك.<br>
يرجى إدخال رمز إعادة التعيين التالي في تطبيق هِجرة.<br>
<br>
رمز إعادة التعيين: <b>{{ $otp }}</b><br>
<br>
إذا لم تطلب إعادة تعيين كلمة المرور هذه، يمكنك تجاهل هذه الرسالة بأمان.<br>
<br>
شكرًا،<br>
{{ __('mail.app-name') }}
</div>

<x-slot:footer>
<x-mail::footer>
© 2025 {{ config('app.name') }}. All rights reserved.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout> 