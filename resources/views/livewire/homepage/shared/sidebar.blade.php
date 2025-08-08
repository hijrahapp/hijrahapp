<div class="kt-sidebar fixed bottom-0 top-0 z-20 hidden shrink-0 flex-col items-stretch border-e border-e-border bg-background [--kt-drawer-enable:true] lg:flex lg:[--kt-drawer-enable:false]"
    data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start top-0 bottom-0" id="sidebar" style="background-color: var(--card); border: unset;">
    <div class="kt-sidebar-header relative hidden shrink-0 items-center justify-between px-3 lg:flex lg:px-6"
        id="sidebar_header">
        <a class="dark:hidden" href="html/demo1.html">
            <img class="default-logo max-w-32" src="/assets/media/app/big-logo.png" />
            <img class="small-logo" style="max-width: 31px;" src="/assets/media/app/logo.png" />
        </a>
        <a class="hidden dark:block" href="html/demo1.html">
            <img class="default-logo max-w-32" src="/assets/media/app/big-logo.png" />
            <img class="small-logo" style="max-width: 31px;" src="/assets/media/app/logo.png" />
        </a>
        <button
            class="kt-btn kt-btn-outline kt-btn-icon absolute start-full top-2/4 -translate-x-2/4 -translate-y-2/4 rtl:translate-x-2/4"
            data-kt-toggle="body" data-kt-toggle-class="kt-sidebar-collapse" id="sidebar_toggle" style="background-color: #F8D87D; border-radius: 100%;">
            <i
                class="ki-filled ki-right kt-toggle-active:rotate-180 rtl:translate rtl:kt-toggle-active:rotate-0 transition-all duration-300 rtl:rotate-180">
            </i>
        </button>
    </div>
    <div class="kt-sidebar-content flex shrink-0 grow py-5 pe-2" id="sidebar_content">
        <div class="kt-scrollable-y-hover flex shrink-0 grow pe-1 ps-2 lg:pe-3 lg:ps-5" data-kt-scrollable="true"
            data-kt-scrollable-dependencies="#sidebar_header" data-kt-scrollable-height="auto"
            data-kt-scrollable-offset="0px" data-kt-scrollable-wrappers="#sidebar_content" id="sidebar_scrollable">
            <div class="kt-menu flex grow flex-col gap-1" data-kt-menu="true" data-kt-menu-accordion-expand-all="false"
                id="sidebar_menu">
                @php $role = session('user')["role"] ?? null; @endphp
                @if($role === 'SuperAdmin' || $role === 'Admin')
                    <div class="kt-menu-item" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                        <div class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px]" tabindex="0">
                            <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                                <i class="ki-filled ki-users text-lg kt-menu-title kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary font-normal text-foreground">
                                </i>
                            </span>
                            <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">
                                Network
                            </span>
                            <span class="kt-menu-arrow text-muted-foreground w-[20px] shrink-0 justify-end ms-1 me-[-10px]">
                                <span class="inline-flex kt-menu-item-show:hidden">
                                    <i class="ki-filled ki-plus text-[11px]">
                                    </i>
                                </span>
                                <span class="hidden kt-menu-item-show:inline-flex">
                                    <i class="ki-filled ki-minus text-[11px]">
                                    </i>
                                </span>
                            </span>
                        </div>
                        <div class="kt-menu-accordion gap-1 ps-[10px] relative before:absolute before:start-[20px] before:top-0 before:bottom-0 before:border-s before:border-border">
                            <div class="kt-menu-item">
                                <a wire:click="setActiveSection('Network', 'Admins Management')" data-kt-container-toggle="#systemAdminsContainer" class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[14px] ps-[10px] pe-[10px] py-[8px]" tabindex="0">
                                    <span class="kt-menu-bullet flex w-[6px] -start-[3px] rtl:start-0 relative before:absolute before:top-0 before:size-[6px] before:rounded-full rtl:before:translate-x-1/2 before:-translate-y-1/2 kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary">
                                    </span>
                                    <span class="kt-menu-title text-2sm font-normal text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary">
                                        Admins Management
                                    </span>
                                </a>
                            </div>
                            <div class="kt-menu-item">
                                <a wire:click="setActiveSection('Network', 'Experts Management')" data-kt-container-toggle="#expertsContainer" class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[14px] ps-[10px] pe-[10px] py-[8px]" tabindex="0">
                                    <span class="kt-menu-bullet flex w-[6px] -start-[3px] rtl:start-0 relative before:absolute before:top-0 before:size-[6px] before:rounded-full rtl:before:translate-x-1/2 before:-translate-y-1/2 kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary">
                                    </span>
                                    <span class="kt-menu-title text-2sm font-normal text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary">
                                        Experts Management
                                    </span>
                                </a>
                            </div>
                            <div class="kt-menu-item">
                                <a wire:click="setActiveSection('Network', 'Customers Management')" data-kt-container-toggle="#customersContainer" class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[14px] ps-[10px] pe-[10px] py-[8px]" tabindex="0">
                                    <span class="kt-menu-bullet flex w-[6px] -start-[3px] rtl:start-0 relative before:absolute before:top-0 before:size-[6px] before:rounded-full rtl:before:translate-x-1/2 before:-translate-y-1/2 kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary">
                                    </span>
                                    <span class="kt-menu-title text-2sm font-normal text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary">
                                        Customers Management
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
                    <div class="kt-menu-item" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                        <div class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px]" tabindex="0">
                            <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                                <i class="ki-filled ki-book text-lg kt-menu-title kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary font-normal text-foreground">
                                </i>
                            </span>
                            <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">
                                Methodologies
                            </span>
                            <span class="kt-menu-arrow text-muted-foreground w-[20px] shrink-0 justify-end ms-1 me-[-10px]">
                                <span class="inline-flex kt-menu-item-show:hidden">
                                    <i class="ki-filled ki-plus text-[11px]">
                                    </i>
                                </span>
                                <span class="hidden kt-menu-item-show:inline-flex">
                                    <i class="ki-filled ki-minus text-[11px]">
                                    </i>
                                </span>
                            </span>
                        </div>
                        <div class="kt-menu-accordion gap-1 ps-[10px] relative before:absolute before:start-[20px] before:top-0 before:bottom-0 before:border-s before:border-border">
                            <div class="kt-menu-item">
                                <a wire:click="setActiveSection('Methodologies', 'Methodologies Management')" data-kt-container-toggle="#methodologiesContainer" class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[14px] ps-[10px] pe-[10px] py-[8px]" tabindex="1">
                                    <span class="kt-menu-bullet flex w-[6px] -start-[3px] rtl:start-0 relative before:absolute before:top-0 before:size-[6px] before:rounded-full rtl:before:translate-x-1/2 before:-translate-y-1/2 kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary">
                                    </span>
                                    <span class="kt-menu-title text-2sm font-normal text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary">
                                        Methodologies Management
                                    </span>
                                </a>
                            </div>
                            @if($role === 'SuperAdmin' || $role === 'Admin')
                            <div class="kt-menu-item">
                                <a wire:click="setActiveSection('Methodologies', 'Pillars Management')" data-kt-container-toggle="#pillarsContainer" class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[14px] ps-[10px] pe-[10px] py-[8px]" tabindex="1">
                                    <span class="kt-menu-bullet flex w-[6px] -start-[3px] rtl:start-0 relative before:absolute before:top-0 before:size-[6px] before:rounded-full rtl:before:translate-x-1/2 before:-translate-y-1/2 kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary">
                                    </span>
                                    <span class="kt-menu-title text-2sm font-normal text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary">
                                        Pillars Management
                                    </span>
                                </a>
                            </div>
                            <div class="kt-menu-item">
                                <a wire:click="setActiveSection('Methodologies', 'Modules Management')" data-kt-container-toggle="#modulesContainer" class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[14px] ps-[10px] pe-[10px] py-[8px]" tabindex="1">
                                    <span class="kt-menu-bullet flex w-[6px] -start-[3px] rtl:start-0 relative before:absolute before:top-0 before:size-[6px] before:rounded-full rtl:before:translate-x-1/2 before:-translate-y-1/2 kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary">
                                    </span>
                                    <span class="kt-menu-title text-2sm font-normal text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary">
                                        Modules Management
                                    </span>
                                </a>
                            </div>
                            <div class="kt-menu-item">
                                <a wire:click="setActiveSection('Methodologies', 'Questions Management')" data-kt-container-toggle="#questionsContainer" class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[14px] ps-[10px] pe-[10px] py-[8px]" tabindex="1">
                                    <span class="kt-menu-bullet flex w-[6px] -start-[3px] rtl:start-0 relative before:absolute before:top-0 before:size-[6px] before:rounded-full rtl:before:translate-x-1/2 before:-translate-y-1/2 kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary">
                                    </span>
                                    <span class="kt-menu-title text-2sm font-normal text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary">
                                        Questions Management
                                    </span>
                                </a>
                            </div>
                            <div class="kt-menu-item">
                                <a wire:click="setActiveSection('Methodologies', 'Tags Management')" data-kt-container-toggle="#tagsContainer" class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[14px] ps-[10px] pe-[10px] py-[8px]" tabindex="1">
                                    <span class="kt-menu-bullet flex w-[6px] -start-[3px] rtl:start-0 relative before:absolute before:top-0 before:size-[6px] before:rounded-full rtl:before:translate-x-1/2 before:-translate-y-1/2 kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary">
                                    </span>
                                    <span class="kt-menu-title text-2sm font-normal text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary">
                                        Tags Management
                                    </span>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>
