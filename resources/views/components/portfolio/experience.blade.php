@props(['experiences'])

@if($experiences->count() > 0)
    <section id="experience" class="py-24 px-6">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold mb-12 text-text-primary">Experience</h2>

            <div class="relative">
                {{-- Timeline line --}}
                <div class="absolute left-6 top-0 bottom-0 w-px bg-gradient-to-b from-accent via-accent/50 to-transparent"></div>

                <div class="space-y-12">
                    @foreach($experiences as $experience)
                        <div class="relative pl-16">
                            {{-- Timeline dot --}}
                            <div class="absolute left-4 top-2 w-4 h-4 rounded-full border-2 border-accent bg-app-bg {{ $experience->is_current ? 'animate-pulse' : '' }}">
                                @if($experience->is_current)
                                    <div class="absolute inset-1 rounded-full bg-accent"></div>
                                @endif
                            </div>

                            {{-- Card --}}
                            <div class="bg-surface border border-white/5 rounded-xl p-6 hover:border-accent/20 transition-all duration-300 group">
                                {{-- Header --}}
                                <div class="flex items-start justify-between gap-4 mb-4">
                                    <div class="flex-1 min-w-0">
                                        {{-- Title --}}
                                        <h3 class="text-lg font-semibold text-text-primary group-hover:text-accent transition-colors">
                                            {{ $experience->title }}
                                        </h3>

                                        {{-- Company & Type --}}
                                        <div class="flex flex-wrap items-center gap-2 mt-1">
                                            @if($experience->company_url)
                                                <a href="{{ $experience->company_url }}" target="_blank" rel="noopener noreferrer" class="text-text-secondary hover:text-accent transition-colors">
                                                    {{ $experience->company }}
                                                </a>
                                            @else
                                                <span class="text-text-secondary">{{ $experience->company }}</span>
                                            @endif

                                            @if($experience->employment_type)
                                                <span class="text-text-muted">•</span>
                                                <span class="text-sm text-text-muted capitalize">{{ str_replace('-', ' ', $experience->employment_type) }}</span>
                                            @endif
                                        </div>

                                        {{-- Period & Duration --}}
                                        <div class="flex flex-wrap items-center gap-2 mt-2 text-sm text-text-muted">
                                            <span>{{ $experience->period_label }}</span>
                                            @if($experience->duration)
                                                <span>•</span>
                                                <span>{{ $experience->duration }}</span>
                                            @endif
                                        </div>

                                        {{-- Location --}}
                                        @if($experience->location)
                                            <div class="flex items-center gap-1 mt-1 text-sm text-text-muted">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                <span>{{ $experience->location }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Current badge --}}
                                    @if($experience->is_current)
                                        <span class="px-2 py-1 text-xs font-medium bg-success/20 text-success rounded-full">
                                            Current
                                        </span>
                                    @endif
                                </div>

                                {{-- Description --}}
                                @if($experience->description)
                                    <p class="text-text-secondary text-sm mb-4 leading-relaxed">
                                        {{ $experience->description }}
                                    </p>
                                @endif

                                {{-- Responsibilities --}}
                                @if($experience->responsibilities && count($experience->responsibilities) > 0)
                                    <ul class="space-y-2 mb-4">
                                        @foreach($experience->responsibilities as $responsibility)
                                            <li class="flex items-start gap-2 text-sm text-text-secondary">
                                                <span class="text-accent mt-1.5">›</span>
                                                <span>{{ is_array($responsibility) ? $responsibility['responsibility'] : $responsibility }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                {{-- Skills --}}
                                @if($experience->skills && count($experience->skills) > 0)
                                    <div class="flex flex-wrap gap-2 pt-4 border-t border-white/5">
                                        @foreach($experience->skills as $skill)
                                            <span class="px-2 py-1 text-xs bg-accent/10 text-accent rounded-md">
                                                {{ $skill }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
