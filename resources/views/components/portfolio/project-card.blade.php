@props(['project'])

<div class="bg-app-bg border border-white/5 rounded-xl p-6 hover:border-accent/30 transition-colors flex flex-col">
    {{-- Badges --}}
    <div class="flex justify-between items-start mb-4">
        <div class="flex gap-2">
            @if ($project->is_featured)
                <span class="inline-flex items-center text-xs bg-yellow-500/20 text-yellow-400 px-2 py-1 rounded">
                    ⭐ Featured
                </span>
            @endif

            @if ($project->is_ongoing)
                <span class="inline-flex items-center text-xs bg-emerald-500/15 text-emerald-400 px-2 py-1 rounded">
                    ⏳ Ongoing
                </span>
            @endif
        </div>
    </div>

    {{-- Title --}}
    <h3 class="text-xl font-semibold mb-2 text-text-primary">
        {{ $project->title }}
    </h3>

    {{-- Meta tags --}}
    <div class="flex flex-wrap gap-2 mb-4 text-xs">
        @if($project->role)
            <span class="px-2 py-1 bg-white/5 text-text-secondary rounded-full">
                Role: <span class="font-medium text-text-primary">{{ $project->role }}</span>
            </span>
        @endif

        @if($project->project_type)
            <span class="px-2 py-1 bg-white/5 text-text-secondary rounded-full">
                {{ ucfirst($project->project_type) }}
            </span>
        @endif

        @if($project->complexity)
            <span class="px-2 py-1 bg-white/5 text-text-secondary rounded-full">
                Complexity: {{ ucfirst($project->complexity) }}
            </span>
        @endif
    </div>

    {{-- Description --}}
    @if($project->description)
        <p class="text-text-secondary mb-4 text-sm leading-relaxed">
            {{ $project->description }}
        </p>
    @endif

    {{-- Impact --}}
    @if($project->impact)
        <div class="mb-4">
            <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Impact</p>
            <p class="text-sm text-text-muted">• {{ $project->impact }}</p>
        </div>
    @endif

    {{-- Responsibilities --}}
    @if($project->responsibilities)
        <div class="mb-4">
            <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Responsibilities</p>
            <ul class="list-disc list-inside text-sm text-text-secondary space-y-1">
                @foreach(preg_split('/\r\n|\r|\n/', $project->responsibilities) as $responsibility)
                    @if(trim($responsibility) !== '')
                        <li>{{ $responsibility }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Languages --}}
    @if(!empty($project->languages))
        <div class="mb-3">
            <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Languages</p>
            <div class="flex flex-wrap gap-2">
                @foreach($project->languages as $lang)
                    <span class="px-3 py-1 bg-indigo-600/20 text-indigo-400 text-xs font-medium rounded-lg">
                        {{ $lang }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tech Stack --}}
    @if(!empty($project->tech_stack))
        <div class="mb-4">
            <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Tech Stack</p>
            <div class="flex flex-wrap gap-2">
                @foreach($project->tech_stack as $tech)
                    <span class="px-3 py-1 bg-accent/10 text-accent text-xs font-mono rounded-lg">
                        {{ $tech }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Timeline --}}
    @if ($project->period_label || $project->duration_label)
        <div class="flex flex-wrap items-center gap-3 text-xs text-text-muted mb-4">
            @if ($project->period_label)
                <span class="inline-flex items-center gap-1">
                    📅 {{ $project->period_label }}
                </span>
            @endif
        </div>
    @endif

    {{-- Links --}}
    @if ($project->code_url || $project->live_url)
        <div class="mt-auto flex gap-4 pt-3 border-t border-white/5">
            @if ($project->code_url)
                <a href="{{ $project->code_url }}" target="_blank"
                   class="text-sm text-accent hover:text-accent-hover transition-colors flex items-center gap-1">
                    <span>View Code</span> <span>→</span>
                </a>
            @endif
            @if ($project->live_url)
                <a href="{{ $project->live_url }}" target="_blank"
                   class="text-sm text-accent hover:text-accent-hover transition-colors flex items-center gap-1">
                    <span>Live Demo</span> <span>→</span>
                </a>
            @endif
        </div>
    @endif
</div>
