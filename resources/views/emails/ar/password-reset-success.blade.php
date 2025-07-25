<x-mail::layout>
<x-slot:header>
<x-mail::header :url="''">
{{ __('mail.app-name') }}
</x-mail::header>
</x-slot:header>

<div dir="rtl">
مرحبًا {{ $user->name ?? $user->email }}،<br>
<br>
لقد تم إعادة تعيين كلمة المرور الخاصة بك بنجاح.
<br>
تم تغيير كلمة مرور حسابك في {{ now()->format('H:i, d M Y') }}<br>
<br>
إذا لم تقم بإعادة تعيين كلمة المرور هذه، فيرجى الاتصال بفريق الدعم لدينا على الفور حيث قد يكون حسابك قد تم اختراقه.<br>
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
