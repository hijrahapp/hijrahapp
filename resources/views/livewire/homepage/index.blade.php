<div>
    @php $role = session('user')["role"] ?? null; @endphp
    @if($role === 'SuperAdmin' || $role === 'Admin')
        <div class="kt-container-fixed kt-main-container" id="systemAdminsContainer">
            <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5 lg:items-end">
                <div class="flex flex-col justify-center gap-2">
                    <h1 class="text-xl font-medium leading-none text-mono">
                        Admins Table
                    </h1>
                </div>
            </div>
            <livewire:homepage.tables.admins-table />
        </div>
    @endif
    @if($role === 'SuperAdmin' || $role === 'Admin')
        <div class="kt-container-fixed kt-main-container hidden" id="expertsContainer">
            <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5 lg:items-end">
                <div class="flex flex-col justify-center gap-2">
                    <h1 class="text-xl font-medium leading-none text-mono">
                        Experts Table
                    </h1>
                </div>
            </div>
            <livewire:homepage.tables.experts-table />
        </div>
    @endif
    @if($role === 'SuperAdmin' || $role === 'Admin')
        <div class="kt-container-fixed kt-main-container hidden" id="customersContainer">
            <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5 lg:items-end">
                <div class="flex flex-col justify-center gap-2">
                    <h1 class="text-xl font-medium leading-none text-mono">
                        Customers Table
                    </h1>
                </div>
            </div>
            <livewire:homepage.tables.customers-table />
        </div>
    @endif
    <!-- Methodologies Container (Expert/Customer default, SuperAdmin/Admin hidden by default) -->
    <div class="kt-container-fixed kt-main-container @if($role === 'SuperAdmin' || $role === 'Admin') hidden @endif" id="methodologiesContainer">
        <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5 lg:items-end">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-medium leading-none text-mono">
                    Methodologies Table
                </h1>
            </div>
        </div>
    </div>
    <div class="kt-container-fixed kt-main-container hidden" id="tagsContainer">
        <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5 lg:items-end">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-medium leading-none text-mono">
                    Tags Table
                </h1>
            </div>
        </div>
        <livewire:homepage.tables.tags-table />
    </div>
</div>
