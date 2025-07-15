<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="en">
<head>
    @include('layouts.partials.head')
    @livewireStyles
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background">
    {{ $slot }}
    @include('layouts.partials.scripts')
    @livewireScripts
</body>
</html> 