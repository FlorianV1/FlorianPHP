@props(['education'])

@if($education && $education->count() > 0)
<section id="education" class="education">
    <div class="pf-container pf-container--narrow-mobile">

        <div class="section-head education__head">
            <h2 class="section-head__title">Education</h2>
            <span class="section-head__slug">/ {{ str_pad($education->count(), 2, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="experience__list">
            @foreach($education as $entry)
                <div class="exp-row{{ !$loop->last ? ' exp-row--divided' : '' }}">

                    <div>
                        <div class="exp-row__period">{{ $entry->period_label }}</div>
                        <div class="exp-row__company">{{ $entry->company }}</div>
                        @if($entry->location)
                            <div class="exp-row__meta">{{ $entry->location }}</div>
                        @endif
                        @if($entry->is_current)
                            <span class="exp-row__current">In progress</span>
                        @endif
                    </div>

                    <div>
                        <h3 class="exp-row__title">{{ $entry->title }}</h3>

                        @if($entry->credential)
                            <p class="exp-row__credential">{{ $entry->credential }}</p>
                        @endif

                        @if($entry->description)
                            <p class="exp-row__desc">{{ $entry->description }}</p>
                        @endif

                        @if($entry->skills && count($entry->skills) > 0)
                            <div class="exp-row__skills">
                                @foreach($entry->skills as $skill)
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
@endif
