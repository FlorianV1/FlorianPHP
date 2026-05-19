@props(['skills'])

@if($skills->count() > 0)
@php
    $skillEmojis = ['PHP'=>'⚡','Laravel'=>'🌿','MySQL'=>'🗄','Redis'=>'🔴','Docker'=>'🐳','Vue.js'=>'🔵','Vue'=>'🔵','Git'=>'🐙','Linux'=>'🐧','Node.js'=>'🟢','Python'=>'🐍','TypeScript'=>'📘','JavaScript'=>'📜','React'=>'⚛'];
@endphp
<section id="skills" style="background:#111114;padding:5rem 0 0;">
    <div style="max-width:1152px;margin:0 auto;padding:0 2.5rem;margin-bottom:2.5rem;">
        <div style="display:flex;align-items:baseline;gap:0.75rem;">
            <h2 style="font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(36px,5vw,56px);letter-spacing:-0.03em;margin:0;color:#f1f5f9;">Technologies</h2>
            <span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:rgba(255,255,255,0.18);margin-bottom:6px;letter-spacing:0.04em;">/ stack</span>
        </div>
    </div>

    {{-- Full-bleed marquee --}}
    <div style="overflow:hidden;border-top:1px solid rgba(255,255,255,0.07);border-bottom:1px solid rgba(255,255,255,0.07);">
        <div class="skills-track" style="display:flex;white-space:nowrap;">
            {{-- First copy --}}
            <div style="display:inline-flex;flex-shrink:0;">
                @foreach($skills as $skill)
                    @php
                        $emoji = $skillEmojis[$skill->name] ?? '·';
                    @endphp
                    <span class="skill-item"
                          style="display:inline-flex;align-items:center;gap:8px;padding:1rem 1.5rem;font-family:'JetBrains Mono',monospace;font-size:13px;letter-spacing:0.04em;color:rgba(255,255,255,0.5);border-right:1px solid rgba(255,255,255,0.07);transition:color 0.2s;cursor:default;white-space:nowrap;"
                          onmouseover="this.style.color='rgba(255,255,255,1)'"
                          onmouseout="this.style.color='rgba(255,255,255,0.5)'">
                        <span style="font-size:15px;">{{ $emoji }}</span>
                        {{ $skill->name }}
                    </span>
                @endforeach
            </div>
            {{-- Second copy (seamless loop) --}}
            <div style="display:inline-flex;flex-shrink:0;" aria-hidden="true">
                @foreach($skills as $skill)
                    @php
                        $emoji = $skillEmojis[$skill->name] ?? '·';
                    @endphp
                    <span class="skill-item"
                          style="display:inline-flex;align-items:center;gap:8px;padding:1rem 1.5rem;font-family:'JetBrains Mono',monospace;font-size:13px;letter-spacing:0.04em;color:rgba(255,255,255,0.5);border-right:1px solid rgba(255,255,255,0.07);transition:color 0.2s;cursor:default;white-space:nowrap;"
                          onmouseover="this.style.color='rgba(255,255,255,1)'"
                          onmouseout="this.style.color='rgba(255,255,255,0.5)'">
                        <span style="font-size:15px;">{{ $emoji }}</span>
                        {{ $skill->name }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <div style="padding-bottom:5rem;"></div>
</section>

{{-- Wave: technologies (#111114) → about (#0b0b0d) --}}
<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:80px;margin-top:-1px;background:#111114;">
    <path d="M0,30 C160,75 320,5 480,45 C640,80 800,10 960,50 C1120,80 1300,15 1440,40 L1440,80 L0,80 Z" fill="#0b0b0d"/>
</svg>

<style>
    .skills-track {
        animation: skillsScroll 30s linear infinite;
    }
    .skills-track:hover {
        animation-play-state: paused;
    }
    @keyframes skillsScroll {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
</style>
@endif
