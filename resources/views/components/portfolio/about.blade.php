@props(['profile'])

@if($profile)
<section id="about" style="background:#0b0b0d;padding:5rem 0 6rem;">
    <div style="max-width:1152px;margin:0 auto;padding:0 2.5rem;">
        <div class="about-grid" style="display:grid;grid-template-columns:1fr 340px;gap:4rem;align-items:start;">

            {{-- Left column --}}
            <div>
                <div style="display:flex;align-items:baseline;gap:0.75rem;margin-bottom:2.5rem;">
                    <h2 style="font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(36px,5vw,56px);letter-spacing:-0.03em;margin:0;color:#f1f5f9;">About me</h2>
                    <span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:rgba(255,255,255,0.18);margin-bottom:6px;letter-spacing:0.04em;">/ me</span>
                </div>

                @if($profile->about_text)
                    <div style="font-family:'JetBrains Mono',monospace;font-size:14px;color:rgba(255,255,255,0.45);line-height:1.8;margin-bottom:1.25rem;">
                        {!! $profile->about_text !!}
                    </div>
                @else
                    <p style="font-family:'JetBrains Mono',monospace;font-size:14px;color:rgba(255,255,255,0.45);line-height:1.8;margin:0 0 1.25rem;">
                        I'm a <strong style="font-weight:500;color:#f1f5f9;">Software Developer</strong> based in the Netherlands, building
                        web applications with a focus on reliability and performance.
                    </p>
                    <p style="font-family:'JetBrains Mono',monospace;font-size:14px;color:rgba(255,255,255,0.45);line-height:1.8;margin:0;">
                        Specialising in <strong style="font-weight:500;color:#f1f5f9;">PHP / Laravel</strong>, I build scalable backends
                        and clean interfaces that are a pleasure to maintain.
                    </p>
                @endif

                {{-- Stats --}}
                @if($profile->stat_1_value || $profile->stat_2_value || $profile->stat_3_value)
                <div style="margin-top:2.5rem;padding-top:2rem;border-top:1px solid rgba(255,255,255,0.07);display:flex;gap:3rem;flex-wrap:wrap;">
                    @if($profile->stat_1_value)
                    <div>
                        <div style="font-family:'Syne',sans-serif;font-weight:800;font-size:28px;color:#f1f5f9;letter-spacing:-0.03em;">{{ $profile->stat_1_value }}</div>
                        <div style="font-family:'JetBrains Mono',monospace;font-size:11px;color:rgba(255,255,255,0.3);margin-top:4px;">{{ $profile->stat_1_label }}</div>
                    </div>
                    @endif
                    @if($profile->stat_2_value)
                    <div>
                        <div style="font-family:'Syne',sans-serif;font-weight:800;font-size:28px;color:#f1f5f9;letter-spacing:-0.03em;">{{ $profile->stat_2_value }}</div>
                        <div style="font-family:'JetBrains Mono',monospace;font-size:11px;color:rgba(255,255,255,0.3);margin-top:4px;">{{ $profile->stat_2_label }}</div>
                    </div>
                    @endif
                    @if($profile->stat_3_value)
                    <div>
                        <div style="font-family:'Syne',sans-serif;font-weight:800;font-size:28px;color:#f1f5f9;letter-spacing:-0.03em;">{{ $profile->stat_3_value }}</div>
                        <div style="font-family:'JetBrains Mono',monospace;font-size:11px;color:rgba(255,255,255,0.3);margin-top:4px;">{{ $profile->stat_3_label }}</div>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Right column: info cards --}}
            <div class="about-sidebar" style="display:flex;flex-direction:column;gap:1rem;margin-top:6rem;">

                <div style="background:var(--bg2);border:1px solid rgba(255,255,255,0.1);border-radius:14px;padding:1.25rem 1.5rem;">
                    <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.25);margin-bottom:0.5rem;">Status</div>
                    <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:18px;color:#86efac;">{{ $profile->status_available ? 'Available' : 'Unavailable' }}</div>
                    <div style="font-family:'JetBrains Mono',monospace;font-size:11px;color:rgba(255,255,255,0.25);margin-top:4px;">{{ $profile->status_text ?? 'open to new projects' }}</div>
                </div>

                @if($profile->location)
                <div style="background:var(--bg2);border:1px solid rgba(255,255,255,0.1);border-radius:14px;padding:1.25rem 1.5rem;">
                    <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.25);margin-bottom:0.5rem;">Location</div>
                    <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:18px;color:#f1f5f9;">{{ $profile->location }}</div>
                    @if($profile->location_timezone)
                    <div style="font-family:'JetBrains Mono',monospace;font-size:11px;color:rgba(255,255,255,0.25);margin-top:4px;">{{ $profile->location_timezone }}</div>
                    @endif
                </div>
                @endif

                @if($profile->about_stack_primary)
                <div style="background:var(--bg2);border:1px solid rgba(255,255,255,0.1);border-radius:14px;padding:1.25rem 1.5rem;">
                    <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.25);margin-bottom:0.5rem;">Stack</div>
                    <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:18px;color:#f1f5f9;">{{ $profile->about_stack_primary }}</div>
                    @if($profile->about_stack_secondary)
                    <div style="font-family:'JetBrains Mono',monospace;font-size:11px;color:rgba(255,255,255,0.25);margin-top:4px;">{{ $profile->about_stack_secondary }}</div>
                    @endif
                </div>
                @endif

            </div>
        </div>
    </div>
</section>

<style>
@media (max-width: 767px) {
    #about > div { padding-left: 1.25rem !important; padding-right: 1.25rem !important; }
    .about-grid { grid-template-columns: 1fr !important; gap: 2rem !important; }
    .about-sidebar { margin-top: 0 !important; }
}
</style>

{{-- Wave: about (#0b0b0d) → contact (#111114) --}}
<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:80px;margin-top:-1px;background:#0b0b0d;">
    <path d="M0,50 C200,10 400,70 600,30 C800,0 1000,60 1200,30 C1320,15 1400,55 1440,45 L1440,80 L0,80 Z" fill="#111114"/>
</svg>
@endif
