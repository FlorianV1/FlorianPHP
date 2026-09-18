@props(['services'])

@if($services && $services->count() > 0)
<section id="services" class="services">
    <div class="pf-container pf-container--narrow-mobile">

        <div class="section-head services__head">
            <h2 class="section-head__title">What I build</h2>
            <span class="section-head__slug">/ services</span>
        </div>

        <div class="services__grid">
            @foreach($services as $service)
                <article class="service-card">
                    @if($service->icon)
                        @php
                            // A typo in the CMS icon field must not take the
                            // homepage down, so an unknown name renders nothing.
                            try {
                                $icon = svg($service->icon, 'service-card__svg')->toHtml();
                            } catch (\Throwable) {
                                $icon = null;
                            }
                        @endphp
                        @if($icon)
                            <span class="service-card__icon" aria-hidden="true">{!! $icon !!}</span>
                        @endif
                    @endif
                    <h3 class="service-card__title">{{ $service->title }}</h3>
                    <p class="service-card__desc">{{ $service->description }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Wave: services (#0b0b0d) → next surface (#111114) --}}
<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="wave wave--from-dark" aria-hidden="true">
    <path d="M0,45 C180,5 380,70 560,35 C760,0 960,65 1160,35 C1290,16 1380,52 1440,38 L1440,80 L0,80 Z" fill="#111114"/>
</svg>
@endif
