<div class="kt-sidebar fixed bottom-0 top-0 z-20 hidden shrink-0 flex-col items-stretch border-e border-e-border bg-background [--kt-drawer-enable:true] lg:flex lg:[--kt-drawer-enable:false]"
    data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start top-0 bottom-0" id="sidebar">
    <div class="kt-sidebar-header relative hidden shrink-0 items-center justify-between px-3 lg:flex lg:px-6"
        id="sidebar_header">
        <a class="dark:hidden" href="html/demo1.html">
            <img class="default-logo min-h-[22px] max-w-none" src="/assets/media/app/default-logo.svg" />
            <img class="small-logo min-h-[22px] max-w-none" src="/assets/media/app/mini-logo.svg" />
        </a>
        <a class="hidden dark:block" href="html/demo1.html">
            <img class="default-logo min-h-[22px] max-w-none" src="/assets/media/app/default-logo-dark.svg" />
            <img class="small-logo min-h-[22px] max-w-none" src="/assets/media/app/mini-logo.svg" />
        </a>
        <button
            class="kt-btn kt-btn-outline kt-btn-icon absolute start-full top-2/4 size-[30px] -translate-x-2/4 -translate-y-2/4 rtl:translate-x-2/4"
            data-kt-toggle="body" data-kt-toggle-class="kt-sidebar-collapse" id="sidebar_toggle">
            <i
                class="ki-filled ki-black-right-line kt-toggle-active:rotate-180 rtl:translate rtl:kt-toggle-active:rotate-0 transition-all duration-300 rtl:rotate-180">
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
                    <div class="kt-menu-item">
                        <a data-kt-container-toggle="#systemAdminsContainer" class="kt-menu-link kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 grow items-center gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] hover:rounded-lg">
                            <span class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                <i class="ki-filled ki-users text-lg kt-menu-title kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary font-normal text-foreground"></i>
                            </span>
                            <span class="kt-menu-title text-m kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary font-normal text-foreground">System Users</span>
                        </a>
                    </div>
                    <div class="kt-menu-item">
                        <a data-kt-container-toggle="#customersContainer" class="kt-menu-link kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 grow items-center gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] hover:rounded-lg">
                            <span class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                <i class="ki-filled ki-address-book text-lg kt-menu-title kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary font-normal text-foreground"></i>
                            </span>
                            <span class="kt-menu-title text-m kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary font-normal text-foreground">Customers</span>
                        </a>
                    </div>
                    <div class="kt-menu-item">
                        <a data-kt-container-toggle="#methodologiesContainer" class="kt-menu-link kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 grow items-center gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] hover:rounded-lg">
                            <span class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                <i class="ki-filled ki-book text-lg kt-menu-title kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary font-normal text-foreground"></i>
                            </span>
                            <span class="kt-menu-title text-m kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary font-normal text-foreground">Methodologies</span>
                        </a>
                    </div>
                @elseif($role === 'Expert' || $role === 'Customer')
                    <div class="kt-menu-item">
                        <a data-kt-container-toggle="#welcomeContainer" class="kt-menu-link kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 grow items-center gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] hover:rounded-lg">
                            <span class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                <i class="ki-filled ki-home text-lg kt-menu-title kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary font-normal text-foreground"></i>
                            </span>
                            <span class="kt-menu-title text-m kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary font-normal text-foreground">Welcome</span>
                        </a>
                    </div>
                    <div class="kt-menu-item">
                        <a data-kt-container-toggle="#methodologiesContainer" class="kt-menu-link kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 grow items-center gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] hover:rounded-lg">
                            <span class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2">
                                <i class="ki-filled ki-book text-lg kt-menu-title kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary font-normal text-foreground"></i>
                            </span>
                            <span class="kt-menu-title text-m kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary font-normal text-foreground">Methodologies</span>
                        </a>
                    </div>
                @endif


            </div>
        </div>
    </div>
</div>
