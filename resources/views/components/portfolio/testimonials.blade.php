@props(['testimonials'])

@if($testimonials && $testimonials->count() > 0)
<section id="testimonials" class="testimonials">
    <div class="pf-container pf-container--narrow-mobile">

        <div class="section-head testimonials__head">
            <h2 class="section-head__title">Kind words</h2>
            <span class="section-head__slug">/ {{ str_pad($testimonials->count(), 2, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="testimonials__grid">
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
                        @if($testimonial->project && $testimonial->project->hasCaseStudy())
                            <a href="{{ route('case-study', $testimonial->project) }}" class="testimonial__project">
                                Read the {{ $testimonial->project->title }} case study →
                            </a>
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- Wave: testimonials (#111114) → experience (#0b0b0d) --}}
<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="wave wave--from-surface" aria-hidden="true">
    <path d="M0,35 C200,75 420,10 620,42 C820,74 1060,8 1440,40 L1440,80 L0,80 Z" fill="#0b0b0d"/>
</svg>
@endif
