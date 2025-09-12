<div class="kt-container-fixed kt-main-container" id="viewUserDetailsContainer">
    <div class="flex flex-wrap items-center gap-2 pb-4">
        <a href="{{ route('liability.users', ['liability' => $liability->id]) }}" class="kt-btn kt-btn-outline flex items-center justify-center">
            <i class="ki-filled ki-arrow-left"></i>
        </a>
        <h1 class="text-xl font-medium leading-none text-mono">
            User Progress for {{ $liability->name }}
        </h1>
    </div>

    <!-- User Overview Section -->
    @php($userProgress = $this->userLiabilityProgress)
    <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-3">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">User Details</h3>
            </div>
            <div class="kt-card-content kt-card-div rounded-b-xl">
                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Liability:</strong> {{ $liability->name }}</p>
                @if($userProgress?->created_at)
                    <p><strong>Started:</strong> {{ $userProgress->created_at->format('Y-m-d, h:ia') }}</p>
                @endif
                @if($userProgress?->updated_at && $userProgress->is_completed)
                    <p><strong>Completed:</strong> {{ $userProgress->updated_at->format('Y-m-d, h:ia') }}</p>
                @endif
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Liability Progress</h3>
            </div>
            <div class="kt-card-content kt-card-div rounded-b-xl">
                @php($progress = $this->liabilityProgress)
                <p><strong>Completion:</strong> {{ $progress['completion_percentage'] }}%</p>
                <p><strong>Todos Completed:</strong> {{ $progress['completed_todos'] }} / {{ $progress['total_todos'] }}</p>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Overall Status</h3>
            </div>
            <div class="kt-card-content kt-card-div rounded-b-xl">
                @php($progress = $this->liabilityProgress)
                <p><strong>Status:</strong> 
                    <span class="px-2 py-1 text-xs rounded {{ $this->getStatusBadgeClass($progress['status']) }}">
                        {{ $this->getStatusLabel($progress['status']) }}
                    </span>
                </p>
                
                <!-- Progress Bar -->
                <div class="mt-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>Progress</span>
                        <span>{{ $progress['completion_percentage'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                             style="width: {{ $progress['completion_percentage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liability Information Section -->
    <div class="mb-8 kt-card">
        <div class="kt-card-header">
            <h2 class="kt-card-title text-lg font-semibold">Liability Information</h2>
        </div>
        <div class="kt-card-content">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="kt-card kt-card-div">
                    <div class="p-4">
                        <h4 class="font-medium mb-2">Basic Information</h4>
                        <div class="space-y-2 text-sm">
                            <p><strong>Name:</strong> {{ $liability->name }}</p>
                            <p><strong>Description:</strong> {{ $liability->description }}</p>
                            @if($liability->title)
                                <p><strong>Title:</strong> {{ $liability->title }}</p>
                            @endif
                            @if($liability->header)
                                <p><strong>Header:</strong> {{ $liability->header }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="kt-card kt-card-div">
                    <div class="p-4">
                        <h4 class="font-medium mb-2">Progress Summary</h4>
                        <div class="space-y-2 text-sm">
                            <p><strong>Total Todos:</strong> {{ $progress['total_todos'] }}</p>
                            <p><strong>Completed Todos:</strong> {{ $progress['completed_todos'] }}</p>
                            <p><strong>Remaining Todos:</strong> {{ $progress['total_todos'] - $progress['completed_todos'] }}</p>
                            <p><strong>Completion Rate:</strong> {{ $progress['completion_percentage'] }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Todos Section -->
    <div class="mb-8 kt-card">
        <div class="kt-card-header">
            <h2 class="kt-card-title text-lg font-semibold">Todos Progress</h2>
        </div>
        <div class="kt-card-content">
            <div class="space-y-4">
                @forelse ($this->todosWithStatus as $todo)
                    <div class="{{ $todo['is_completed'] ? 'bg-green-50 text-green-600' : 'bg-gray-50 text-gray-600' }} kt-card-accent kt-card rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium
                                    {{ $todo['is_completed'] ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                                    @if($todo['is_completed'])
                                        <i class="ki-filled ki-check text-xs"></i>
                                    @else
                                        {{ $todo['id'] + 1 }}
                                    @endif
                                </span>
                                <div class="flex-1">
                                    <p class="font-medium">
                                        {{ $todo['title'] }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                                    {{ $todo['is_completed'] ? 'Completed' : 'Pending' }}
                                </span>
                                <i class="ki-filled {{ $todo['is_completed'] ? 'ki-check-circle text-green-500' : 'ki-time text-gray-400' }}"></i>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="flex flex-col items-center gap-2">
                            <i class="ki-filled ki-file-down text-gray-400 text-3xl"></i>
                            <span class="text-gray-500">No todos found for this liability</span>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
