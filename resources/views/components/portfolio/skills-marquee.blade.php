@props(['skills'])

@if($skills->count() > 0)
    <section class="py-16 bg-surface overflow-hidden">
{{--        <div class="max-w-6xl mx-auto px-6 mb-8">--}}
{{--            <h2 class="text-3xl font-bold text-text-primary">Technologies I Work With</h2>--}}
{{--        </div>--}}

        <div class="relative">
            <div class="absolute left-0 top-0 bottom-0 w-32 bg-gradient-to-r from-surface to-transparent z-10"></div>
            <div class="absolute right-0 top-0 bottom-0 w-32 bg-gradient-to-l from-surface to-transparent z-10"></div>

            <div class="flex marquee-container">
                <div class="flex items-center gap-12 marquee-content">
                    @foreach($skills as $skill)
                        <a href="{{ $skill->url ?? '#' }}" target="_blank" rel="noopener noreferrer" class="flex-shrink-0 group" title="{{ $skill->name }}">
                            @if($skill->logo)
                                <img src="{{ asset('storage/' . $skill->logo) }}" alt="{{ $skill->name }}" class="h-10 w-10 object-contain opacity-60 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300">
                            @elseif($skill->icon)
                                <i class="{{ $skill->icon }} text-4xl opacity-60 grayscale group-hover:opacity-100 group-hover:grayscale-0 transition-all duration-300"></i>
                            @else
                                <span class="text-text-muted group-hover:text-accent transition-colors text-sm font-medium">{{ $skill->name }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>

                <div class="flex items-center gap-12 marquee-content">
                    @foreach($skills as $skill)
                        <a href="{{ $skill->url ?? '#' }}" target="_blank" rel="noopener noreferrer" class="flex-shrink-0 group" title="{{ $skill->name }}">
                            @if($skill->logo)
                                <img src="{{ asset('storage/' . $skill->logo) }}" alt="{{ $skill->name }}" class="h-10 w-10 object-contain opacity-60 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300">
                            @elseif($skill->icon)
                                <i class="{{ $skill->icon }} text-4xl opacity-60 grayscale group-hover:opacity-100 group-hover:grayscale-0 transition-all duration-300"></i>
                            @else
                                <span class="text-text-muted group-hover:text-accent transition-colors text-sm font-medium">{{ $skill->name }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <style>
        .marquee-container {
            animation: marquee 30s linear infinite;
        }
        .marquee-container:hover {
            animation-play-state: paused;
        }
        .marquee-content {
            padding-right: 3rem;
        }
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
    </style>
@endif
