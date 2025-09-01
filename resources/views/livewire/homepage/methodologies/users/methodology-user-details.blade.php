<div class="kt-container-fixed kt-main-container" id="viewUserAnswersContainer">
    <div class="flex flex-wrap items-center gap-2 pb-4">
        <a href="{{ route('methodology.users', ['methodology' => $methodology->id]) }}" class="kt-btn kt-btn-outline flex items-center justify-center">
            <i class="ki-filled ki-arrow-left"></i>
        </a>
        <h1 class="text-xl font-medium leading-none text-mono">
            User Answers for {{ $methodology->name }}
        </h1>
    </div>

    <!-- User Overview Section -->
    <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-3">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">User Details</h3>
            </div>
            <div class="kt-card-content kt-card-div rounded-b-xl">
                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Type:</strong> <span class="capitalize">{{ str_replace(['twoSection'], ['Two Section'], $methodology->type) }}</span></p>
                @php($methodologyStatus = $this->getMethodologyStatus())
                <p><strong>Status:</strong> 
                    <span class="px-2 py-1 text-xs rounded {{ $this->getStatusBadgeClass($methodologyStatus) }}">
                        {{ $this->getStatusLabel($methodologyStatus) }}
                    </span>
                </p>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">General Questions</h3>
            </div>
            <div class="kt-card-content kt-card-div rounded-b-xl">
                <p><strong>Percentage:</strong> {{ number_format($this->generalResults['percentage'] ?? 0, 2) }}%</p>
                <p><strong>Answered:</strong> {{ $this->generalResults['answered_questions'] ?? 0 }} / {{ $this->generalResults['total_questions'] ?? 0 }}</p>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Overall Score</h3>
            </div>
            <div class="kt-card-content kt-card-div rounded-b-xl">
                @php($overallData = $this->overallScoreData)
                <p><strong>{{ $overallData['label'] }}:</strong> {{ number_format($overallData['percentage'], 2) }}%</p>
                <p><strong>Count:</strong> {{ $overallData['count'] }}</p>
            </div>
        </div>
    </div>

    <!-- General Questions Section -->
    @if($this->generalQuestions->isNotEmpty())
    <div class="mb-8 kt-card">
        <div class="kt-card-header">
            <h2 class="kt-card-title text-lg font-semibold">General Questions Responses</h2>
        </div>
        <div class="kt-card-content">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach ($this->generalQuestions as $question)
                    @php($meta = $this->getGeneralQuestionMeta($question->id))
                    <div class="kt-card kt-card-div">
                        <div class="kt-card-content p-4">
                            <h4 class="font-medium mb-2">{{ $question->title }}</h4>
                            <div class="text-xs text-secondary-foreground mb-2">
                                Question Weight: {{ $meta['question_weight'] ?? '—' }}
                            </div>
                            @php($ua = $this->userAnswersAll->first(fn($a) => $a->context_type==='methodology' && (int)$a->context_id===$methodology->id && (int)$a->question_id===$question->id))
                            @if ($ua)
                                <p><strong>Answer:</strong> {{ $ua->answer->title }}</p>
                                <div class="flex justify-between text-xs text-secondary-foreground mt-1">
                                    <span>Answer Weight: {{ $meta['answer_weight'] ?? '—' }}</span>
                                    <span>Score: {{ isset($meta['score']) ? number_format($meta['score'], 2) : '—' }}</span>
                                </div>
                                <p class="text-xs text-secondary-foreground mt-1">{{ optional($meta['answered_at'])->format('Y-m-d, h:ia') }}</p>
                            @else
                                <p class="text-gray-500 italic">No answer provided</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @php($structure = $this->methodologyStructure)

    @if($structure['type'] === 'simple')
        <!-- Simple Type: Direct Modules -->
        <div class="mb-8 kt-card">
            <div class="kt-card-header">
                <h2 class="kt-card-title text-lg font-semibold">Module Responses</h2>
            </div>
            <div class="kt-card-content">
                @foreach ($structure['modules'] as $module)
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-4">
                            <h3 class="text-md font-medium">{{ $module->name }}</h3>
                            @php($pct = $this->getModulePercentage($module->id))
                            @if(!is_null($pct))
                                <span class="text-xs px-2 py-1 bg-green-100 rounded">{{ number_format($pct, 2) }}%</span>
                            @endif
                            @php($moduleStatus = $this->getModuleStatus($module->id))
                            <span class="px-2 py-1 text-xs rounded {{ $this->getStatusBadgeClass($moduleStatus) }}">
                                {{ $this->getStatusLabel($moduleStatus) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                            @foreach ($module->questions as $question)
                                @php($meta = $this->getModuleQuestionMeta($module->id, $question->pivot->pillar_id ?? null, $question->id))
                                <div class="kt-card kt-card-div">
                                    <div class="kt-card-content p-3">
                                        <h5 class="font-medium text-sm mb-2">{{ $question->title }}</h5>
                                        <div class="text-xs text-secondary-foreground mb-2">Weight: {{ $meta['question_weight'] ?? '—' }}</div>
                                        @php($ua = $this->userAnswersAll->first(fn($a) => $a->context_type==='module' && (int)$a->context_id===$module->id && (int)$a->question_id===$question->id))
                                        @if ($ua)
                                            <p class="text-sm"><strong>Answer:</strong> {{ $ua->answer->title }}</p>
                                            <div class="text-xs text-secondary-foreground mt-1">
                                                <div>Answer Weight: {{ $meta['answer_weight'] ?? '—' }}</div>
                                                <div>{{ optional($meta['answered_at'])->format('Y-m-d, h:ia') }}</div>
                                            </div>
                                        @else
                                            <p class="text-gray-500 italic text-sm">No answer</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    @elseif($structure['type'] === 'complex')
        <!-- Complex Type: Pillars → Modules -->
        <div class="mb-8 kt-card">
            <div class="kt-card-header">
                <h2 class="kt-card-title text-lg font-semibold">Pillar & Module Responses</h2>
            </div>
            <div class="kt-card-content">
                @foreach ($structure['pillars'] as $pillar)
                    <div class="mb-8 border rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-4">
                            <h3 class="text-lg font-medium">{{ $pillar->name }}</h3>
                            @php($ppct = $this->getPillarPercentage($pillar->id))
                            @if(!is_null($ppct))
                                <span class="text-sm px-3 py-1 bg-blue-100 rounded">{{ number_format($ppct, 2) }}%</span>
                            @endif
                            @php($pillarStatus = $this->getPillarStatus($pillar->id))
                            <span class="px-2 py-1 text-xs rounded {{ $this->getStatusBadgeClass($pillarStatus) }}">
                                {{ $this->getStatusLabel($pillarStatus) }}
                            </span>
                        </div>

                        @foreach ($pillar->modules as $module)
                            <div class="ml-4 mb-6">
                                <div class="flex items-center gap-2 mb-3">
                                    <h4 class="text-md font-medium">{{ $module->name }}</h4>
                                    @php($pct = $this->getModulePercentage($module->id))
                                    @if(!is_null($pct))
                                        <span class="text-xs px-2 py-1 bg-green-100 rounded">{{ number_format($pct, 2) }}%</span>
                                    @endif
                                    @php($moduleStatus = $this->getModuleStatus($module->id, $pillar->id))
                                    <span class="px-2 py-1 text-xs rounded {{ $this->getStatusBadgeClass($moduleStatus) }}">
                                        {{ $this->getStatusLabel($moduleStatus) }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($module->questions as $question)
                                        @php($meta = $this->getModuleQuestionMeta($module->id, $question->pivot->pillar_id ?? null, $question->id))
                                        <div class="kt-card kt-card-div">
                                            <div class="kt-card-content p-3">
                                                <h6 class="font-medium text-sm mb-2">{{ $question->title }}</h6>
                                                <div class="text-xs text-secondary-foreground mb-2">Weight: {{ $meta['question_weight'] ?? '—' }}</div>
                                                @php($ua = $this->userAnswersAll->first(fn($a) => $a->context_type==='module' && (int)$a->context_id===$module->id && (int)$a->question_id===$question->id))
                                                @if ($ua)
                                                    <p class="text-sm"><strong>Answer:</strong> {{ $ua->answer->title }}</p>
                                                    <div class="text-xs text-secondary-foreground mt-1">
                                                        <div>Answer Weight: {{ $meta['answer_weight'] ?? '—' }}</div>
                                                        <div>{{ optional($meta['answered_at'])->format('Y-m-d, h:ia') }}</div>
                                                    </div>
                                                @else
                                                    <p class="text-gray-500 italic text-sm">No answer</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

    @elseif($structure['type'] === 'twoSection')
        <!-- Two-Section Type: Sections → Pillars → Modules -->
        <div class="mb-8 kt-card">
            <div class="kt-card-header">
                <h2 class="kt-card-title text-lg font-semibold">Section, Pillar & Module Responses</h2>
            </div>
            <div class="kt-card-content">
                @foreach ($structure['sections'] as $sectionKey => $section)
                    <div class="mb-10 border-2 rounded-lg p-6">
                        <div class="flex items-center gap-2 mb-6">
                            <h2 class="text-xl font-bold">{{ $section['name'] }}</h2>
                            @php($spct = $this->getSectionPercentage($section['name']))
                            @if(!is_null($spct))
                                <span class="text-sm px-3 py-1 bg-purple-100 rounded font-medium">{{ number_format($spct, 2) }}%</span>
                            @endif
                            @php($sectionStatus = $this->getSectionStatus($sectionKey))
                            <span class="px-2 py-1 text-xs rounded {{ $this->getStatusBadgeClass($sectionStatus) }}">
                                {{ $this->getStatusLabel($sectionStatus) }}
                            </span>
                        </div>

                        @foreach ($section['pillars'] as $pillar)
                            <div class="ml-4 mb-8 border rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-4">
                                    <h3 class="text-lg font-medium">{{ $pillar->name }}</h3>
                                    @php($ppct = $this->getPillarPercentage($pillar->id))
                                    @if(!is_null($ppct))
                                        <span class="text-sm px-3 py-1 bg-blue-100 rounded">{{ number_format($ppct, 2) }}%</span>
                                    @endif
                                    @php($pillarStatus = $this->getPillarStatus($pillar->id))
                                    <span class="px-2 py-1 text-xs rounded {{ $this->getStatusBadgeClass($pillarStatus) }}">
                                        {{ $this->getStatusLabel($pillarStatus) }}
                                    </span>
                                </div>

                                @foreach ($pillar->modules as $module)
                                    <div class="ml-4 mb-6">
                                        <div class="flex items-center gap-2 mb-3">
                                            <h4 class="text-md font-medium">{{ $module->name }}</h4>
                                            @php($pct = $this->getModulePercentage($module->id))
                                            @if(!is_null($pct))
                                                <span class="text-xs px-2 py-1 bg-green-100 rounded">{{ number_format($pct, 2) }}%</span>
                                            @endif
                                            @php($moduleStatus = $this->getModuleStatus($module->id, $pillar->id))
                                            <span class="px-2 py-1 text-xs rounded {{ $this->getStatusBadgeClass($moduleStatus) }}">
                                                {{ $this->getStatusLabel($moduleStatus) }}
                                            </span>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                                            @foreach ($module->questions as $question)
                                                @php($meta = $this->getModuleQuestionMeta($module->id, $question->pivot->pillar_id ?? null, $question->id))
                                                <div class="kt-card kt-card-div">
                                                    <div class="kt-card-content p-3">
                                                        <h6 class="font-medium text-sm mb-2">{{ $question->title }}</h6>
                                                        <div class="text-xs text-secondary-foreground mb-2">Weight: {{ $meta['question_weight'] ?? '—' }}</div>
                                                        @php($ua = $this->userAnswersAll->first(fn($a) => $a->context_type==='module' && (int)$a->context_id===$module->id && (int)$a->question_id===$question->id))
                                                        @if ($ua)
                                                            <p class="text-sm"><strong>Answer:</strong> {{ $ua->answer->title }}</p>
                                                            <div class="text-xs text-secondary-foreground mt-1">
                                                                <div>Answer Weight: {{ $meta['answer_weight'] ?? '—' }}</div>
                                                                <div>{{ optional($meta['answered_at'])->format('Y-m-d, h:ia') }}</div>
                                                            </div>
                                                        @else
                                                            <p class="text-gray-500 italic text-sm">No answer</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Chart Section -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Performance Chart</h3>
        </div>
        <div class="kt-card-content">
            <div id="chart"></div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.syncfusion.com/ej2/dist/ej2.min.js"></script>
        <script>
            document.addEventListener('livewire:load', function () {
                var chartData = @json($this->chartData);

                var chart = new ej.charts.Chart({
                    series: [{
                        dataSource: chartData,
                        xName: 'x',
                        yName: 'y',
                        type: 'Pie'
                    }],
                    legendSettings: { visible: true },
                    title: 'Performance Breakdown'
                });
                chart.appendTo('#chart');
            });
        </script>
    @endpush
</div>