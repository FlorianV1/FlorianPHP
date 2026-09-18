@props(['skills'])

@if($skills->count() > 0)
@php
    $skillEmojis = ['PHP'=>'⚡','Laravel'=>'🌿','MySQL'=>'🗄','Redis'=>'🔴','Docker'=>'🐳','Vue.js'=>'🔵','Vue'=>'🔵','Git'=>'🐙','Linux'=>'🐧','Node.js'=>'🟢','Python'=>'🐍','TypeScript'=>'📘','JavaScript'=>'📜','React'=>'⚛'];
@endphp
<section id="skills" class="skills">
    <div class="pf-container pf-container--narrow-mobile skills__head-wrap">
        <div class="section-head">
            <h2 class="section-head__title">Technologies</h2>
            <span class="section-head__slug">/ stack</span>
        </div>
    </div>

    {{-- Full-bleed marquee --}}
    <div class="skills__marquee">
        <div class="skills-track">
            {{-- First copy --}}
            <div class="skills__copy">
                @foreach($skills as $skill)
                    <span class="skill-item">
                        <span class="skill-item__emoji">{{ $skillEmojis[$skill->name] ?? '·' }}</span>
                        {{ $skill->name }}
                    </span>
                @endforeach
            </div>
            {{-- Second copy (seamless loop) --}}
            <div class="skills__copy" aria-hidden="true">
                @foreach($skills as $skill)
                    <span class="skill-item">
                        <span class="skill-item__emoji">{{ $skillEmojis[$skill->name] ?? '·' }}</span>
                        {{ $skill->name }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="skills__tail"></div>
</section>

{{-- Wave: technologies (#111114) → about (#0b0b0d) --}}
<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="wave wave--from-surface">
    <path d="M0,30 C160,75 320,5 480,45 C640,80 800,10 960,50 C1120,80 1300,15 1440,40 L1440,80 L0,80 Z" fill="#0b0b0d"/>
</svg>
@endif
