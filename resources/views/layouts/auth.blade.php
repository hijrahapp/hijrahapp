<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="en">
<head>
    @include('layouts.partials.head')
    @livewireStyles
    <style>
    .page-bg {
        background-image: url('/assets/media/images/2600x1200/bg-5.png');
    }
    .dark .page-bg {
        background-image: url('/assets/media/images/2600x1200/bg-5-dark.png');
    }
    </style>
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background page-bg">
    {{ $slot }}
    @include('layouts.partials.scripts')
    @livewireScripts
</body>
</html> 