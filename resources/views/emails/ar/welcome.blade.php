<x-mail::layout>
<x-slot:header>
<x-mail::header :url="''">
{{ __('mail.app-name') }}
</x-mail::header>
</x-slot:header>

<div dir="rtl">
مرحباً {{ $user->name ?? $user->email }}،<br>
سعداء بانضمامك إلى هجـرة – مساحة تساعدك تتوقف للحظة، تفكر، وتبدأ خطوات حقيقية لتحسين حياتك<br>
<br>
إليك ما يمكنك فعله الآن:<br>
- تعرّف على نفسك أكثر<br>
- استكشف أين تشعر بالتوازن وأين تحتاج لدعم أكثر<br>
- احصل على نصائح وبرامج مفيدة تناسبك تمامًا<br>
<br>
إذا كان لديك أي استفسار أو تحتاج للمساعدة، نحن دائمًا هنا من أجلك.<br>
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