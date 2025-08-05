<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="en">
    <head>
        @include('layouts.partials.head')
        @livewireStyles
    </head>
    <body class="demo1 kt-sidebar-fixed kt-sidebar-collapse kt-header-fixed flex h-full bg-background text-base text-foreground antialiased">
        <livewire:shared.modals.search />
        <livewire:homepage.modals.user-profile />
        <livewire:homepage.modals.change-password />
        <livewire:homepage.modals.user-add-modal />
        <livewire:homepage.modals.user-edit-modal />
        <livewire:homepage.modals.user-status-modal />
        <livewire:homepage.modals.expert-add-modal />
        <livewire:homepage.modals.tag-add-modal />
        <livewire:homepage.modals.confirmation-modal />
        <!-- Page -->
        <!-- Main -->
        <div class="flex grow">
            <livewire:homepage.shared.sidebar />

            <!-- Wrapper -->
            <div class="kt-wrapper flex grow flex-col">
                @persist('mega-menu')
                <livewire:homepage.shared.header />
                @endpersist

                <!-- Content -->
                <main class="grow pt-5" style="background-color: var(--secondary)" id="content" role="content">
                    {{ $slot }}
                </main>
                <!-- End of Content -->

                <livewire:homepage.shared.footer />
            </div>
            <!-- End of Wrapper -->
        </div>
        <!-- End of Main -->
        <!-- End of Page -->

        @include('layouts.partials.scripts')
        @livewireScripts
    </body>
</html>
