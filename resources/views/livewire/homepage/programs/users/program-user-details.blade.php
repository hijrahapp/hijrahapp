<div class="kt-container-fixed kt-main-container" id="viewUserAnswersContainer">
    <div class="flex flex-wrap items-center gap-2 pb-4">
        <a href="{{ route('program.users', ['program' => $program->id]) }}" class="kt-btn kt-btn-outline flex items-center justify-center">
            <i class="ki-filled ki-arrow-left"></i>
        </a>
        <h1 class="text-xl font-medium leading-none text-mono">
            User Progress for {{ $program->name }}
        </h1>
    </div>

    <!-- User Overview Section -->
    @php($userProgram = $this->userProgram)
    <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-3">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">User Details</h3>
            </div>
            <div class="kt-card-content kt-card-div rounded-b-xl">
                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Program:</strong> {{ $program->name }}</p>
                @if($userProgram?->started_at)
                    <p><strong>Started:</strong> {{ $userProgram->started_at->format('Y-m-d, h:ia') }}</p>
                @endif
                @if($userProgram?->completed_at)
                    <p><strong>Completed:</strong> {{ $userProgram->completed_at->format('Y-m-d, h:ia') }}</p>
                @endif
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Program Progress</h3>
            </div>
            <div class="kt-card-content kt-card-div rounded-b-xl">
                @php($progress = $this->programProgress)
                <p><strong>Completion:</strong> {{ $progress['completion_percentage'] }}%</p>
                <p><strong>Steps Completed:</strong> {{ $progress['completed_steps'] }} / {{ $progress['total_steps'] }}</p>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Overall Status</h3>
            </div>
            <div class="kt-card-content kt-card-div rounded-b-xl">
                @php($progress = $this->programProgress)
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

    <!-- Steps Section -->
    <div class="mb-8 kt-card">
        <div class="kt-card-header">
            <h2 class="kt-card-title text-lg font-semibold">Step Progress</h2>
        </div>
        <div class="kt-card-content">
            <div class="space-y-6">
                @foreach ($this->steps as $step)
                    @php($stepData = $this->getStepData($step->id))
                    <div class="kt-card-accent kt-card rounded-lg p-4 mb-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-medium">{{ $step->name }}</h3>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($stepData['status'] === 'completed')
                                    bg-green-50 text-green-600
                                    @elseif($stepData['status'] === 'in_progress')
                                    bg-yellow-50 text-yellow-600
                                    @elseif($stepData['status'] === 'skipped')
                                    bg-orange-50 text-orange-600
                                    @else
                                    bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $this->getStatusLabel($stepData['status']) }}
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-600">
                                    {{ $this->getStepTypeDisplayName($step->type) }}
                                </span>
                            </div>
                            @if($stepData['progress']?->completed_at)
                                <span class="text-sm text-gray-500">
                                    Completed: {{ $stepData['progress']->completed_at->format('Y-m-d, h:ia') }}
                                </span>
                            @endif
                        </div>

                        @if($step->description)
                            <p class="text-gray-600 mb-4">{{ $step->description }}</p>
                        @endif

                        <!-- Step-specific content -->
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @if($step->type === 'journal')
                                <div class="kt-card kt-card-div">
                                    <div class="p-4">
                                        <h4 class="font-medium mb-2">Journal Entry</h4>
                                        @if($stepData['user_input'])
                                            <p class="text-sm bg-gray-50 p-3 rounded">{{ $stepData['user_input'] }}</p>
                                        @else
                                            <p class="text-gray-500 italic">No journal entry provided</p>
                                        @endif
                                    </div>
                                </div>

                            @elseif($step->type === 'quiz')
                                <div class="kt-card kt-card-div">
                                    <div class="p-4">
                                        <h4 class="font-medium mb-2">Quiz Results</h4>
                                        @if($stepData['score'] !== null)
                                            <div class="space-y-4">
                                                <div class="grid grid-cols-3 gap-4 text-sm">
                                                    <div class="text-center p-2 bg-violet-50 rounded">
                                                        <div class="font-medium text-violet-600">{{ $stepData['user_input']['questions_count'] ?? 0 }}</div>
                                                        <div class="text-xs text-gray-600">Questions</div>
                                                    </div>
                                                    <div class="text-center p-2 bg-blue-50 rounded">
                                                        <div class="font-medium text-blue-600">{{ $stepData['score'] }}</div>
                                                        <div class="text-xs text-gray-600">Score</div>
                                                    </div>
                                                    <div class="text-center p-2 bg-green-50 rounded">
                                                        <div class="font-medium text-green-600">{{ $stepData['percentage'] }}%</div>
                                                        <div class="text-xs text-gray-600">Percentage</div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Quiz Progress Bar -->
                                                <div>
                                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                                        <span>Quiz Score</span>
                                                        <span>{{ $stepData['percentage'] }}%</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="bg-green-600 h-2 rounded-full transition-all duration-300" 
                                                             style="width: {{ $stepData['percentage'] }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-gray-500 italic">Quiz not completed</p>
                                        @endif
                                    </div>
                                </div>

                            @elseif($step->type === 'challenge')
                                <div class="kt-card kt-card-div">
                                    <div class="p-4">
                                        <h4 class="font-medium mb-2">Challenge Progress</h4>
                                        @if($stepData['user_input'])
                                            <div class="space-y-4">
                                                <div class="grid grid-cols-3 gap-4 text-sm">
                                                    <div class="text-center p-2 bg-violet-50 rounded">
                                                        <div class="font-medium text-violet-600">{{ $stepData['user_input']['total_challenges'] }}</div>
                                                        <div class="text-xs text-gray-600">Challenges</div>
                                                    </div>
                                                    <div class="text-center p-2 bg-blue-50 rounded">
                                                        <div class="font-medium text-blue-600">{{ $stepData['user_input']['completed_challenges'] }}</div>
                                                        <div class="text-xs text-gray-600">Completed</div>
                                                    </div>
                                                    <div class="text-center p-2 bg-green-50 rounded">
                                                        <div class="font-medium text-green-600">{{ $stepData['percentage'] }}%</div>
                                                        <div class="text-xs text-gray-600">Progress</div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Challenge Progress Bar -->
                                                <div>
                                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                                        <span>Challenge Progress</span>
                                                        <span>{{ $stepData['percentage'] }}%</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="bg-violet-600 h-2 rounded-full transition-all duration-300" 
                                                             style="width: {{ $stepData['percentage'] }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-gray-500 italic">Challenge not started</p>
                                        @endif
                                    </div>
                                </div>

                            @else
                                <!-- For article, advice, daily_mission, video, audio, book -->
                                <div class="kt-card kt-card-div">
                                    <div class="p-4">
                                        <h4 class="font-medium mb-2">{{ $this->getStepTypeDisplayName($step->type) }} Content</h4>
                                        @if($step->content)
                                            <div class="text-sm text-gray-600 max-h-32 overflow-y-auto">
                                                {{ Str::limit($step->content, 200) }}
                                            </div>
                                        @endif
                                        @if($step->content_url)
                                            <div class="mt-2">
                                                <a href="{{ $step->content_url }}" target="_blank" class="text-blue-600 hover:underline text-sm">
                                                    View Content <i class="ki-filled ki-exit-up-right"></i>
                                                </a>
                                            </div>
                                        @endif
                                        <p class="text-gray-500 italic text-sm mt-2">No user input required</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Step Info -->
                            <div class="kt-card kt-card-div">
                                <div class="p-4">
                                    <h4 class="font-medium mb-2">Step Information</h4>
                                    <div class="space-y-2 text-sm">
                                        <p><strong>Type:</strong> {{ $this->getStepTypeDisplayName($step->type) }}</p>
                                        @if($step->time_to_finish)
                                            <p><strong>Duration:</strong> {{ $step->time_to_finish }} {{ $step->time_type }}</p>
                                        @endif
                                        @if($stepData['progress']?->started_at)
                                            <p><strong>Started:</strong> {{ $stepData['progress']->started_at->format('Y-m-d, h:ia') }}</p>
                                        @endif
                                        @if($stepData['progress']?->completed_at)
                                            <p><strong>Completed:</strong> {{ $stepData['progress']->completed_at->format('Y-m-d, h:ia') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($step->type === 'quiz' &&isset($stepData['user_input']['questions']) && count($stepData['user_input']['questions']) > 0)
                            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-1">
                                <div class="kt-card kt-card-div">
                                    <div class="p-4">
                                        <h5 class="font-medium text-sm mb-3">Questions & Answers:</h5>
                                        <div class="space-y-3">
                                            @foreach($stepData['user_input']['questions'] as $index => $qa)
                                                <div class="border rounded-lg p-3 {{ $qa['is_correct'] ? 'bg-green-50 border-green-200' : 'bg-destructive/5 border-destructive/10' }}">
                                                    <div class="flex items-start gap-2">
                                                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium
                                                            {{ $qa['is_correct'] ? 'bg-green-500 text-white' : 'bg-destructive text-white' }}">
                                                            {{ $index + 1 }}
                                                        </span>
                                                        <div class="flex-1">
                                                            <p class="font-medium text-sm mb-1">{{ $qa['question']->title }}</p>
                                                            @if($qa['user_answer'])
                                                                <p class="text-sm {{ $qa['is_correct'] ? 'text-green-700' : 'text-red-700' }}">
                                                                    <strong>Your Answer:</strong> {{ $qa['user_answer']->title }}
                                                                    @if($qa['is_correct'])
                                                                        <i class="ki-filled ki-check-circle text-green-600 ml-1"></i>
                                                                    @else
                                                                        <i class="ki-filled ki-cross-circle text-destructive ml-1"></i>
                                                                    @endif
                                                                </p>
                                                            @else
                                                                <p class="text-sm text-gray-500 italic">No answer provided</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($step->type === 'challenge' && isset($stepData['user_input']['challenge_details']) && count($stepData['user_input']['challenge_details']) > 0)
                            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-1">
                                <div class="kt-card kt-card-div">
                                    <div class="p-4">
                                    <h5 class="font-medium text-sm mb-3">Challenge Status:</h5>
                                        <div class="space-y-2">
                                            @foreach($stepData['user_input']['challenge_details'] as $challenge)
                                                <div class="flex items-center gap-3 p-3 rounded-lg border {{ $challenge['is_completed'] ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}">
                                                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium
                                                        {{ $challenge['is_completed'] ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                                                        @if($challenge['is_completed'])
                                                            <i class="ki-filled ki-check text-xs"></i>
                                                        @else
                                                            {{ $challenge['index'] }}
                                                        @endif
                                                    </span>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-medium {{ $challenge['is_completed'] ? 'text-green-800' : 'text-gray-700' }}">
                                                            {{ $challenge['title'] }}
                                                        </p>
                                                        <p class="text-xs {{ $challenge['is_completed'] ? 'text-green-600' : 'text-gray-500' }}">
                                                            {{ $challenge['is_completed'] ? 'Completed' : 'Not completed' }}
                                                        </p>
                                                    </div>
                                                    @if($challenge['is_completed'])
                                                        <i class="ki-filled ki-check-circle text-green-500"></i>
                                                    @else
                                                        <i class="ki-filled ki-time text-gray-400"></i>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
