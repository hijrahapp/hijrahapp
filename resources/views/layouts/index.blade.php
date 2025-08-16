<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="en">
    <head>
        @include('layouts.partials.head')
        @livewireStyles
    </head>
    <body class="demo1 kt-sidebar-fixed kt-sidebar-collapse kt-header-fixed flex h-full bg-background text-base text-foreground antialiased">
        <livewire:shared.modals.search />
        <livewire:shared.modals.confirmation-modal />
        <livewire:homepage.shared.user-profile />
        <livewire:homepage.shared.change-password />

        <div class="flex grow">
            <livewire:homepage.shared.sidebar />

            <div class="kt-wrapper flex grow flex-col">
                @persist('mega-menu')
                <livewire:homepage.shared.header />
                @endpersist

                <main class="grow pt-5" style="background-color: var(--secondary)" id="content" role="content">
                    {{ $slot }}
                </main>

                <livewire:homepage.shared.footer />
            </div>
        </div>

        @include('layouts.partials.scripts')
        @livewireScripts
    </body>
</html>
