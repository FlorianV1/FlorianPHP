@php
    use Illuminate\Support\Facades\Storage;

    $metaTitle = $project->meta_title ?: $project->title . ' — case study';
    $metaDescription = $project->meta_description ?: $project->outcome ?: $project->description;
    $shots = collect($project->screenshots ?? []);
@endphp

<x-layouts.portfolio
    :profile="$profile"
    :colors="$colors"
    :seoTitle="$metaTitle"
    :seoDescription="$metaDescription"
    seoType="article"
    :seoImage="$shots->first()"
>
    <article class="case">
        <header class="case__header">
            <div class="pf-container pf-container--narrow-mobile">
                <a href="{{ route('home') }}#projects" class="case__back">← All projects</a>

                <h1 class="case__title">{{ $project->title }}</h1>

                <dl class="case__facts">
                    @if($project->role)
                        <div class="case__fact">
                            <dt>Role</dt>
                            <dd>{{ $project->role }}</dd>
                        </div>
                    @endif
                    @if($project->period_label ?? $project->started_at)
                        <div class="case__fact">
                            <dt>Timeline</dt>
                            <dd>
                                {{ optional($project->started_at)->format('M Y') }}
                                — {{ $project->is_ongoing ? 'Present' : optional($project->finished_at)->format('M Y') }}
                            </dd>
                        </div>
                    @endif
                    @if($project->project_type)
                        <div class="case__fact">
                            <dt>Type</dt>
                            <dd>{{ ucfirst(str_replace('_', ' ', $project->project_type)) }}</dd>
                        </div>
                    @endif
                </dl>

                @if($project->outcome)
                    <p class="case__outcome">{{ $project->outcome }}</p>
                @endif

                @if($project->live_url || $project->code_url)
                    <div class="case__links">
                        @if($project->live_url)
                            <a href="{{ $project->live_url }}" target="_blank" rel="noopener noreferrer" class="hero__cta hero__cta--primary">
                                Visit {{ $project->title }}
                            </a>
                        @endif
                        @if($project->code_url)
                            <a href="{{ $project->code_url }}" target="_blank" rel="noopener noreferrer" class="hero__cta hero__cta--secondary">
                                View the code
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </header>

        @if($shots->isNotEmpty())
            <div class="pf-container pf-container--narrow-mobile">
                <div class="case__shots">
                    @foreach($shots as $shot)
                        <img src="{{ Storage::url($shot) }}"
                             alt="{{ $project->title }} screenshot {{ $loop->iteration }}"
                             class="case__shot"
                             loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                             decoding="async">
                    @endforeach
                </div>
            </div>
        @endif

        <div class="pf-container pf-container--narrow-mobile">
            <div class="case__body">
                {!! $project->case_study_body !!}
            </div>

            @if(!empty($project->tech_stack))
                <div class="case__stack">
                    <h2 class="case__stack-title">Built with</h2>
                    <div class="exp-row__skills">
                        @foreach($project->tech_stack as $tech)
                            <span class="tag">{{ $tech }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($testimonials->isNotEmpty())
                <div class="case__quotes">
                    @foreach($testimonials as $testimonial)
                        <figure class="testimonial">
                            <blockquote class="testimonial__quote">
                                <p>{{ $testimonial->quote }}</p>
                            </blockquote>
                            <figcaption class="testimonial__by">
                                <span class="testimonial__name">{{ $testimonial->author_name }}</span>
                                @if($testimonial->attribution)
                                    <span class="testimonial__role">{{ $testimonial->attribution }}</span>
                                @endif
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            @endif
        </div>

        @if($related->isNotEmpty())
            <div class="pf-container pf-container--narrow-mobile">
                <div class="case__related">
                    <h2 class="case__stack-title">Other projects</h2>
                    <div class="case__related-grid">
                        @foreach($related as $other)
                            <div class="proj-card">
                                <h3 class="proj-card__title proj-card__title--sm">{{ $other->title }}</h3>
                                @if($other->description)
                                    <p class="proj-card__desc proj-card__desc--sm">{{ Str::limit($other->description, 120) }}</p>
                                @endif
                                <div class="proj-card__links">
                                    @if($other->hasCaseStudy())
                                        <a href="{{ route('case-study', $other) }}" class="proj-card__link proj-card__link--primary">Read case study →</a>
                                    @elseif($other->live_url)
                                        <a href="{{ $other->live_url }}" target="_blank" rel="noopener noreferrer" class="proj-card__link">Visit {{ $other->title }} →</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="case__cta">
            <div class="pf-container pf-container--narrow-mobile">
                <h2 class="case__cta-title">Got something similar in mind?</h2>
                <a href="{{ route('home') }}#contact" class="hero__cta hero__cta--primary">Get in touch</a>
            </div>
        </div>
    </article>
</x-layouts.portfolio>
