@props(['experiences'])

@if($experiences->count() > 0)
<section id="experience" class="experience">
    <div class="pf-container pf-container--narrow-mobile">

        {{-- Heading --}}
        <div class="section-head experience__head">
            <h2 class="section-head__title">Experience</h2>
            <span class="section-head__slug">/ {{ str_pad($experiences->count(), 2, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="experience__list">
            @foreach($experiences as $experience)
                <div class="exp-row{{ !$loop->last ? ' exp-row--divided' : '' }}">

                    {{-- Left: date, company, meta --}}
                    <div>
                        <div class="exp-row__period">
                            {{ $experience->period_label }}
                        </div>
                        <div class="exp-row__company">
                            {{ $experience->company }}
                        </div>
                        @if($experience->employment_type || $experience->location)
                            <div class="exp-row__meta">
                                {{ $experience->employment_type ? str_replace('-', ' ', $experience->employment_type) : '' }}
                                {{ ($experience->employment_type && $experience->location) ? ' · ' : '' }}
                                {{ $experience->location ?? '' }}
                            </div>
                        @endif
                        @if($experience->is_current)
                            <span class="exp-row__current">Current</span>
                        @endif
                    </div>

                    {{-- Right: title, description, bullets, skills --}}
                    <div>
                        <h3 class="exp-row__title">{{ $experience->title }}</h3>

                        @if($experience->description)
                            <p class="exp-row__desc">{{ $experience->description }}</p>
                        @endif

                        @if($experience->responsibilities && count($experience->responsibilities) > 0)
                            <ul class="exp-row__duties">
                                @foreach($experience->responsibilities as $responsibility)
                                    <li class="exp-row__duty">
                                        <span class="exp-row__duty-marker">›</span>
                                        {{ is_array($responsibility) ? $responsibility['responsibility'] : $responsibility }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if($experience->skills && count($experience->skills) > 0)
                            <div class="exp-row__skills">
                                @foreach($experience->skills as $skill)
                                    <span class="tag">{{ $skill }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Wave: experience (#0b0b0d) → technologies (#111114) --}}
<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="wave wave--from-dark">
    <path d="M0,55 C120,10 300,70 480,30 C660,0 840,65 1020,35 C1200,10 1340,60 1440,40 L1440,80 L0,80 Z" fill="#111114"/>
</svg>
@endif
