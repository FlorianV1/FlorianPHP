@props(['projects'])

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@if($projects->count() > 0)
<section id="projects" class="projects">
    <div class="pf-container pf-container--narrow-mobile">

        {{-- Heading --}}
        <div class="section-head projects__head">
            <h2 class="section-head__title">Projects</h2>
            <span class="section-head__slug">/ {{ str_pad($projects->count(), 2, '0', STR_PAD_LEFT) }}</span>
        </div>

        {{-- Three-column staggered grid --}}
        <div id="projects-grid" class="projects__grid">
            @foreach($projects->take(3)->values() as $index => $project)
                @php
                    $offset = $index === 1 ? ' proj-item--offset-down' : ($index === 2 ? ' proj-item--offset-up' : '');
                    $num = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                    $thumb = collect($project->screenshots ?? [])->first();
                @endphp
                <div class="proj-item{{ $offset }}">
                    @if($project->is_featured)
                        <span class="proj-badge">★ Featured</span>
                    @endif

                    <div class="proj-card">

                        @if($thumb)
                            <img src="{{ Storage::url($thumb) }}"
                                 alt="Screenshot of {{ $project->title }}"
                                 class="proj-card__shot"
                                 loading="lazy"
                                 decoding="async">
                        @endif

                        <span class="proj-card__num">{{ $num }}</span>

                        <h3 class="proj-card__title">{{ $project->title }}</h3>

                        @if($project->role)
                            <p class="proj-card__role">{{ $project->role }}</p>
                        @endif

                        @if($project->description)
                            <p class="proj-card__desc">{{ $project->description }}</p>
                        @endif

                        @if($project->outcome)
                            <p class="proj-card__outcome">
                                <span class="proj-card__outcome-label">Outcome</span>
                                {{ $project->outcome }}
                            </p>
                        @endif

                        {{-- Tags --}}
                        @if(!empty($project->tech_stack))
                            <div class="proj-card__tags">
                                @foreach($project->tech_stack as $tech)
                                    <span class="tag">{{ $tech }}</span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Links --}}
                        <div class="proj-card__links">
                            @if($project->hasCaseStudy())
                                <a href="{{ route('case-study', $project) }}" class="proj-card__link proj-card__link--primary">
                                    Read case study →
                                </a>
                            @endif
                            @if($project->live_url || $project->code_url)
                                <a href="{{ $project->live_url ?? $project->code_url }}" target="_blank" rel="noopener noreferrer" class="proj-card__link">
                                    Visit {{ $project->title }} →
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Remaining projects in a simpler 2-col layout if more than 3 --}}
        @if($projects->count() > 3)
            <div id="projects-grid-extra" class="projects__grid-extra">
                @foreach($projects->skip(3) as $project)
                    <div class="proj-card">
                        <h3 class="proj-card__title proj-card__title--sm">{{ $project->title }}</h3>
                        @if($project->role)
                            <p class="proj-card__role">{{ $project->role }}</p>
                        @endif
                        @if($project->description)
                            <p class="proj-card__desc proj-card__desc--sm">{{ $project->description }}</p>
                        @endif
                        <div class="proj-card__links">
                            @if($project->hasCaseStudy())
                                <a href="{{ route('case-study', $project) }}" class="proj-card__link proj-card__link--primary">
                                    Read case study →
                                </a>
                            @endif
                            @if($project->live_url || $project->code_url)
                                <a href="{{ $project->live_url ?? $project->code_url }}" target="_blank" rel="noopener noreferrer" class="proj-card__link">
                                    Visit {{ $project->title }} →
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- Wave: projects (#111114) → next section (#0b0b0d) --}}
<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="wave wave--from-surface" aria-hidden="true">
    <path d="M0,20 C200,70 400,0 600,35 C800,70 1100,5 1440,45 L1440,80 L0,80 Z" fill="#0b0b0d"/>
</svg>
@endif
