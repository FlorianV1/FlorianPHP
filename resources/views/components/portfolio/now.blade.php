@props(['items'])

@if($items->count() > 0)
    <section id="now" class="now">
        <div class="pf-container">
            <div class="section-head section-head--sm now__head">
                <h2 class="section-head__title">Now</h2>
                <span class="section-head__slug">/ current focus</span>
            </div>
            <div class="now__list">
                @foreach($items as $item)
                    <div class="now__item">
                        <span class="now__marker">→</span>
                        <p class="now__text">{{ $item->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
