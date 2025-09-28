<div class="kt-container-fixed kt-main-container" id="userFeedbackDetailsContainer">
    <div class="flex flex-wrap items-center justify-between gap-2 pb-4">
        <div class="flex items-center gap-2">
            <button wire:click="backToUserList" class="kt-btn kt-btn-outline flex items-center justify-center">
                <i class="ki-filled ki-arrow-left"></i>
            </button>
            <h1 class="text-xl font-medium leading-none text-mono">
                Feedback Details
            </h1>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-3">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">User Details</h3>
            </div>
            <div class="kt-card-content kt-card-div rounded-b-xl">
                <p class="mb-2"><strong>Name:</strong> {{ $user->name }}</p>
                <p class="mb-2"><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Submission Date:</strong> {{ $feedback->submitted_at->format('F j, Y \a\t g:i A') }}</p>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Program Details</h3>
            </div>
            <div class="kt-card-content kt-card-div rounded-b-xl">
                <p class="mb-2"><strong>Name:</strong> {{ $program->name }}</p>
                <p class="mb-2"><strong>Status:</strong> 
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $program->active ? 'bg-green-50 text-green-600' : 'bg-gray-50 text-gray-600' }}">
                        {{ $program->active ? 'Active' : 'Inactive' }}
                    </span>
                </p>
                @if($program->description)
                <p><strong>Description:</strong> {{ Str::limit($program->description, 200) }}</p>
                @endif
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Form Details</h3>
            </div>
            <div class="kt-card-content kt-card-div rounded-b-xl">
                <p class="mb-2"><strong>Title:</strong> {{ $feedbackForm->name }}</p>
                <p class="mb-2"><strong>Version:</strong> {{ $feedbackForm->version }}</p>
                @if($feedbackForm->description)
                <p><strong>Description:</strong> {{ $feedbackForm->description }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Questions & Answers Section -->
    <div class="kt-card mb-5">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Questions & Answers ({{ count($questions ?? []) }})</h3>
        </div>
        <div class="kt-card-body p-5 pb-0">
            @if(empty($questions))
                <div class="text-center py-8 text-muted-foreground">
                    <i class="ki-filled ki-message-question text-4xl mb-4"></i>
                    <p class="text-lg font-medium">No questions found</p>
                    <p class="text-sm">This form does not contain any questions.</p>
                </div>
            @else
                @foreach($questions as $index => $question)
                    <div class="kt-card kt-card-div mb-5">
                        <div class="kt-card-body p-4">
                            <div class="flex items-start gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="font-bold text-lg">{{ $index + 1 }}:</span>
                                        <span class="font-medium text-lg">{{ $question['text'] }}</span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($question['type'] === 'rating')
                                            bg-blue-50 text-blue-600
                                            @elseif($question['type'] === 'text')
                                            bg-gray-50 text-gray-600
                                            @else
                                            bg-green-50 text-green-600
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $question['type'])) }}
                                        </span>
                                        @if($question['required'] ?? false)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-destructive/5 text-destructive">
                                                Required
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Question Details -->
                                    @if($question['type'] === 'rating')
                                        <div class="text-sm text-gray-700 mb-3">
                                            <p class="font-medium">Rating scale: {{ $question['min_value'] ?? 1 }} to {{ $question['max_value'] ?? 5 }}</p>
                                        </div>
                                    @elseif(in_array($question['type'], ['single_choice', 'multiple_choice']) && !empty($question['options']))
                                        <div class="text-sm text-gray-700 mb-3">
                                            <p class="font-medium">Available Options:</p>
                                            <div class="flex flex-wrap gap-2 mt-2">
                                                @foreach($question['options'] as $option)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                                        {{ is_array($option) ? ($option['label'] ?? $option['value'] ?? '') : $option }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($question['type'] === 'text' && isset($question['max_length']))
                                        <div class="text-sm text-gray-700 mb-3">
                                            <p class="font-medium">Maximum length: {{ $question['max_length'] }} characters</p>
                                        </div>
                                    @endif

                                    <!-- User's Answer -->
                                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                        <label class="block text-sm font-medium mb-2 text-primary">User's Answer:</label>
                                        @php
                                            $response = $this->getResponseValue($question['key']);
                                            $formattedResponse = $this->getFormattedResponseValue($question, $response);
                                        @endphp
                                        
                                        @if($question['type'] === 'rating' && $response)
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-lg">{{ $response }}</span>
                                                <div class="flex">
                                                    @for($i = 1; $i <= ($question['max_value'] ?? 5); $i++)
                                                        <i class="ki-filled ki-star text-sm 
                                                            @if($i <= $response) text-yellow-500
                                                            @else text-gray-300
                                                            @endif">
                                                        </i>
                                                    @endfor
                                                </div>
                                                <span class="text-sm text-muted-foreground">out of {{ $question['max_value'] ?? 5 }}</span>
                                            </div>
                                        @else
                                            <p class="text-sm 
                                                @if($response) text-gray-900
                                                @else text-gray-500 italic
                                                @endif">
                                                {{ $formattedResponse }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
