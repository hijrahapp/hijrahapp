<x-mail::layout>
<x-slot:header>
<x-mail::header :url="''">
{{ __('mail.app-name') }}
</x-mail::header>
</x-slot:header>

<div dir="rtl">
مرحباً {{ $user->name ?? $user->email }}،<br>
<br>
نرحب بك في لوحة تحكم هجـرة!<br>
تم منحك صلاحية الدخول كـ{{ __('mail.'.$role) }}، ونحن سعداء بانضمامك إلى الفريق.<br>
<br>
بيانات الدخول الخاصة بك:<br>
- رابط اللوحة: {{ config('app.url') }}<br>
- اسم المستخدم (البريد الإلكتروني): {{ $user->email }}<br>
- كلمة المرور المؤقتة: <b>{{ $password }}</b><br>
<br>
🔒 مهم:<br>
هذه كلمة مرور مؤقتة، ويرجى تغييرها مباشرة بعد تسجيل الدخول لضمان أمان حسابك.<br>
<br>
لأي استفسارات أو دعم، لا تتردد في التواصل معنا.<br>
<br>
مرحبًا بك معنا!<br>
{{ __('mail.admin-signature') }}
</div>

<x-slot:footer>
<x-mail::footer>
© 2025 {{ config('app.name') }}. All rights reserved.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
