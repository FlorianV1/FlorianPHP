@props(['experiences'])

@if($experiences->count() > 0)
<section id="experience" style="background:#0b0b0d;padding:5rem 0 6rem;">
    <div style="max-width:1152px;margin:0 auto;padding:0 2.5rem;">

        {{-- Heading --}}
        <div style="display:flex;align-items:baseline;gap:0.75rem;margin-bottom:3.5rem;">
            <h2 style="font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(36px,5vw,56px);letter-spacing:-0.03em;margin:0;color:#f1f5f9;">Experience</h2>
            <span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:rgba(255,255,255,0.18);margin-bottom:6px;letter-spacing:0.04em;">/ 02</span>
        </div>

        <div style="display:flex;flex-direction:column;gap:0;">
            @foreach($experiences as $experience)
                <div style="display:grid;grid-template-columns:200px 1fr;gap:3rem;padding:2.5rem 0;{{ !$loop->last ? 'border-bottom:1px solid rgba(255,255,255,0.06);' : '' }}">

                    {{-- Left: date, company, meta --}}
                    <div>
                        <div style="font-family:'JetBrains Mono',monospace;font-size:11px;color:rgba(255,255,255,0.3);letter-spacing:0.06em;margin-bottom:0.6rem;line-height:1.6;">
                            {{ $experience->period_label }}
                        </div>
                        <div style="font-family:'Syne',sans-serif;font-weight:600;font-size:14px;color:rgba(255,255,255,0.5);margin-bottom:0.35rem;">
                            {{ $experience->company }}
                        </div>
                        @if($experience->employment_type || $experience->location)
                            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.25);margin-bottom:0.75rem;line-height:1.6;">
                                {{ $experience->employment_type ? str_replace('-', ' ', $experience->employment_type) : '' }}
                                {{ ($experience->employment_type && $experience->location) ? ' · ' : '' }}
                                {{ $experience->location ?? '' }}
                            </div>
                        @endif
                        @if($experience->is_current)
                            <span style="font-family:'JetBrains Mono',monospace;font-size:10px;padding:3px 8px;background:rgba(134,239,172,0.12);border:1px solid rgba(134,239,172,0.2);border-radius:9999px;color:#86efac;letter-spacing:0.04em;">Current</span>
                        @endif
                    </div>

                    {{-- Right: title, description, bullets, skills --}}
                    <div>
                        <h3 style="font-family:'Syne',sans-serif;font-weight:700;font-size:20px;letter-spacing:-0.02em;color:#f1f5f9;margin:0 0 0.75rem;">{{ $experience->title }}</h3>

                        @if($experience->description)
                            <p style="font-family:'JetBrains Mono',monospace;font-size:13px;color:rgba(255,255,255,0.45);line-height:1.75;margin:0 0 1rem;">{{ $experience->description }}</p>
                        @endif

                        @if($experience->responsibilities && count($experience->responsibilities) > 0)
                            <ul style="list-style:none;margin:0 0 1.25rem;padding:0;">
                                @foreach($experience->responsibilities as $responsibility)
                                    <li style="position:relative;padding-left:14px;font-family:'JetBrains Mono',monospace;font-size:12px;color:rgba(255,255,255,0.4);line-height:1.75;margin-bottom:0.2rem;">
                                        <span style="position:absolute;left:0;color:rgba(255,255,255,0.4);">›</span>
                                        {{ is_array($responsibility) ? $responsibility['responsibility'] : $responsibility }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if($experience->skills && count($experience->skills) > 0)
                            <div style="display:flex;flex-wrap:wrap;gap:0.4rem;">
                                @foreach($experience->skills as $skill)
                                    <span style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.04em;padding:2px 8px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.12);border-radius:9999px;color:rgba(255,255,255,0.45);">{{ $skill }}</span>
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
<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:80px;margin-top:-1px;background:#0b0b0d;">
    <path d="M0,55 C120,10 300,70 480,30 C660,0 840,65 1020,35 C1200,10 1340,60 1440,40 L1440,80 L0,80 Z" fill="#111114"/>
</svg>
@endif
