<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="en">
    <head>
        @include('layouts.partials.head')
        @livewireStyles
    </head>
    <body class="demo1 kt-sidebar-fixed kt-sidebar-collapse kt-header-fixed flex h-full bg-background text-base text-foreground antialiased">
        <livewire:shared.theme-toggle />
        <livewire:shared.modals.search />
        <livewire:shared.modals.user-profile />
        <livewire:shared.modals.change-password />
        <livewire:demo1.user-add-modal />
        <livewire:demo1.user-edit-modal />
        <livewire:demo1.user-status-modal />
        <!-- Page -->
        <!-- Main -->
        <div class="flex grow">
            <livewire:demo1.sidebar />

            <!-- Wrapper -->
            <div class="kt-wrapper flex grow flex-col">
                @persist('mega-menu')
                <livewire:demo1.header />
                @endpersist

                <!-- Content -->
                <main class="grow pt-5" id="content" role="content">
                    {{ $slot }}
                </main>
                <!-- End of Content -->

                <livewire:demo1.footer />
            </div>
            <!-- End of Wrapper -->
        </div>
        <!-- End of Main -->
        <!-- End of Page -->

        @include('layouts.partials.scripts')
        @livewireScripts
    </body>
</html>
