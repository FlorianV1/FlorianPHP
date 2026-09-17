@props(['profile', 'stats' => null])

@php
    // The `stats` table is the source of truth. The legacy profiles.stat_* text
    // columns are the fallback so the block never empties out before the rows
    // have been created in Filament.
    $statRows = collect($stats ?? [])
        ->map(fn ($stat) => ['value' => $stat->display_value, 'label' => $stat->display_label]);

    if ($statRows->isEmpty() && $profile) {
        $statRows = collect([1, 2, 3])
            ->map(fn ($i) => [
                'value' => $profile->{"stat_{$i}_value"},
                'label' => $profile->{"stat_{$i}_label"},
            ])
            ->filter(fn ($row) => filled($row['value']))
            ->values();
    }
@endphp

@if($profile)
<section id="about" class="about">
    <div class="pf-container pf-container--narrow-mobile">
        <div class="about-grid">

            {{-- Left column --}}
            <div>
                <div class="section-head about__head">
                    <h2 class="section-head__title">About me</h2>
                    <span class="section-head__slug">/ me</span>
                </div>

                @if($profile->about_text)
                    <div class="about__body">
                        {!! $profile->about_text !!}
                    </div>
                @else
                    <p class="about__para">
                        I'm a <strong>Software Developer</strong> based in the Netherlands, building
                        web applications with a focus on reliability and performance.
                    </p>
                    <p class="about__para about__para--last">
                        Specialising in <strong>PHP / Laravel</strong>, I build scalable backends
                        and clean interfaces that are a pleasure to maintain.
                    </p>
                @endif

                {{-- Stats --}}
                @if($statRows->isNotEmpty())
                <div class="about__stats">
                    @foreach($statRows as $stat)
                        <div>
                            <div class="about__stat-value">{{ $stat['value'] }}</div>
                            <div class="about__stat-label">{{ $stat['label'] }}</div>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Right column: info cards --}}
            <div class="about-sidebar">

                <div class="about-card">
                    <div class="about-card__label">Status</div>
                    <div class="about-card__value about-card__value--ok">{{ $profile->status_available ? 'Available' : 'Unavailable' }}</div>
                    <div class="about-card__note">{{ $profile->status_text ?? 'open to new projects' }}</div>
                </div>

                @if($profile->location)
                <div class="about-card">
                    <div class="about-card__label">Location</div>
                    <div class="about-card__value">{{ $profile->location }}</div>
                    @if($profile->location_timezone_label)
                    <div class="about-card__note">{{ $profile->location_timezone_label }}</div>
                    @endif
                </div>
                @endif

                @if($profile->about_stack_primary)
                <div class="about-card">
                    <div class="about-card__label">Stack</div>
                    <div class="about-card__value">{{ $profile->about_stack_primary }}</div>
                    @if($profile->about_stack_secondary)
                    <div class="about-card__note">{{ $profile->about_stack_secondary }}</div>
                    @endif
                </div>
                @endif

            </div>
        </div>
    </div>
</section>

{{-- Wave: about (#0b0b0d) → contact (#111114) --}}
<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="wave wave--from-dark">
    <path d="M0,50 C200,10 400,70 600,30 C800,0 1000,60 1200,30 C1320,15 1400,55 1440,45 L1440,80 L0,80 Z" fill="#111114"/>
</svg>
@endif
