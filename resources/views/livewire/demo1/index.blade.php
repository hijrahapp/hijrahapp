<div>
    @php $role = session('user')["role"] ?? null; @endphp
    <!-- Welcome/Methodologies Container for Expert -->
    @if($role === 'Expert')
        <div class="kt-container-fixed kt-main-container" id="welcomeContainer">
            <div class="flex flex-col items-center justify-center py-16">
                <h1 class="text-2xl font-bold mb-4">Welcome to Hijrah App</h1>
                <p class="text-lg">Let's start to add some methodologies.</p>
            </div>
        </div>
    @endif
    <!-- Users Container (SuperAdmin/Admin only) -->
    @if($role === 'SuperAdmin' || $role === 'Admin')
        <div class="kt-container-fixed kt-main-container" id="systemAdminsContainer">
            <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5 lg:items-end">
                <div class="flex flex-col justify-center gap-2">
                    <h1 class="text-xl font-medium leading-none text-mono">
                        System Users
                    </h1>
                    <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                        View and edit users
                    </div>
                </div>
            </div>
            <livewire:demo1.system-admin-table />
        </div>
    @endif
    @if($role === 'SuperAdmin' || $role === 'Admin')
        <div class="kt-container-fixed kt-main-container hidden" id="expertsContainer">
            <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5 lg:items-end">
                <div class="flex flex-col justify-center gap-2">
                    <h1 class="text-xl font-medium leading-none text-mono">
                        Experts
                    </h1>
                    <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                        View and edit experts
                    </div>
                </div>
            </div>
            <livewire:demo1.experts-table />
        </div>
    @endif
    @if($role === 'SuperAdmin' || $role === 'Admin')
        <div class="kt-container-fixed kt-main-container hidden" id="customersContainer">
            <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5 lg:items-end">
                <div class="flex flex-col justify-center gap-2">
                    <h1 class="text-xl font-medium leading-none text-mono">
                        Customers
                    </h1>
                    <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                        View and edit customers
                    </div>
                </div>
            </div>
            <livewire:demo1.customer-table />
        </div>
    @endif
    <!-- Methodologies Container (Expert/Customer default, SuperAdmin/Admin hidden by default) -->
    @if($role === 'Expert' || $role === 'Customer' || $role === 'SuperAdmin' || $role === 'Admin')
        <div class="kt-container-fixed kt-main-container hidden" id="methodologiesContainer">
            <div class="flex flex-col items-center justify-center py-16">
                <h1 class="text-2xl font-bold mb-4">Methodologies</h1>
                <p class="text-lg">Here you can manage and view methodologies. (Placeholder)</p>
            </div>
        </div>
    @endif
</div>
