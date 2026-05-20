@props(['projects'])

@if($projects->count() > 0)
<section id="projects" style="background:#111114;padding:5rem 0 6rem;">
    <div style="max-width:1152px;margin:0 auto;padding:0 2.5rem;">

        {{-- Heading --}}
        <div style="display:flex;align-items:baseline;gap:0.75rem;margin-bottom:3rem;">
            <h2 style="font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(36px,5vw,56px);letter-spacing:-0.03em;margin:0;color:#f1f5f9;">Projects</h2>
            <span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:rgba(255,255,255,0.18);margin-bottom:6px;letter-spacing:0.04em;">/ {{ str_pad($projects->count(), 2, '0', STR_PAD_LEFT) }}</span>
        </div>

        {{-- Three-column staggered grid --}}
        <div id="projects-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;align-items:start;">
            @foreach($projects->take(3)->values() as $index => $project)
                @php
                    $mt = $index === 1 ? '2.5rem' : ($index === 2 ? '-1rem' : '0');
                    $num = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                @endphp
                <div class="proj-item" style="position:relative;margin-top:{{ $mt }};">
                    @if($project->is_featured)
                        <span style="position:absolute;top:-10px;right:18px;font-family:'JetBrains Mono',monospace;font-size:10px;font-weight:600;padding:3px 10px;background:#ffffff;color:#0b0b0d;border-radius:9999px;z-index:1;white-space:nowrap;">★ Featured</span>
                    @endif

                    <div class="proj-card" style="background:var(--bg);border:1px solid rgba(255,255,255,0.12);border-radius:16px;padding:1.75rem;transition:transform 0.25s ease,border-color 0.25s ease,box-shadow 0.25s ease;cursor:default;">

                        <span style="font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.1em;color:rgba(255,255,255,0.18);display:block;margin-bottom:0.75rem;">{{ $num }}</span>

                        <h3 style="font-family:'Syne',sans-serif;font-weight:800;font-size:22px;color:#f1f5f9;margin:0 0 0.75rem;">{{ $project->title }}</h3>

                        @if($project->description)
                            <p style="font-family:'JetBrains Mono',monospace;font-size:12px;color:rgba(255,255,255,0.45);line-height:1.65;margin:0 0 1.25rem;">{{ $project->description }}</p>
                        @endif

                        {{-- Tags --}}
                        @if(!empty($project->tech_stack))
                            <div style="display:flex;flex-wrap:wrap;gap:0.4rem;margin-bottom:1.25rem;">
                                @foreach($project->tech_stack as $tech)
                                    <span style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.04em;padding:2px 8px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.12);border-radius:9999px;color:rgba(255,255,255,0.45);">{{ $tech }}</span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Link --}}
                        @if($project->live_url || $project->code_url)
                            <a href="{{ $project->live_url ?? $project->code_url }}" target="_blank"
                               style="font-family:'JetBrains Mono',monospace;font-size:12px;color:rgba(255,255,255,0.35);text-decoration:none;transition:color 0.2s;"
                               onmouseover="this.style.color='rgba(255,255,255,1)'"
                               onmouseout="this.style.color='rgba(255,255,255,0.35)'">
                                View project →
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Remaining projects in a simpler 2-col layout if more than 3 --}}
        @if($projects->count() > 3)
            <div id="projects-grid-extra" style="display:grid;grid-template-columns:repeat(2,1fr);gap:1.5rem;margin-top:1.5rem;">
                @foreach($projects->skip(3) as $project)
                    <div class="proj-card" style="background:var(--bg);border:1px solid rgba(255,255,255,0.12);border-radius:16px;padding:1.75rem;transition:transform 0.25s ease,border-color 0.25s ease;">
                        <h3 style="font-family:'Syne',sans-serif;font-weight:800;font-size:20px;color:#f1f5f9;margin:0 0 0.6rem;">{{ $project->title }}</h3>
                        @if($project->description)
                            <p style="font-family:'JetBrains Mono',monospace;font-size:12px;color:rgba(255,255,255,0.45);line-height:1.65;margin:0 0 1rem;">{{ $project->description }}</p>
                        @endif
                        @if($project->live_url || $project->code_url)
                            <a href="{{ $project->live_url ?? $project->code_url }}" target="_blank"
                               style="font-family:'JetBrains Mono',monospace;font-size:12px;color:rgba(255,255,255,0.35);text-decoration:none;transition:color 0.2s;"
                               onmouseover="this.style.color='rgba(255,255,255,1)'"
                               onmouseout="this.style.color='rgba(255,255,255,0.35)'">
                                View project →
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- Wave: projects (#111114) → experience (#0b0b0d) --}}
<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:80px;margin-top:-1px;background:#111114;">
    <path d="M0,20 C200,70 400,0 600,35 C800,70 1100,5 1440,45 L1440,80 L0,80 Z" fill="#0b0b0d"/>
</svg>

<style>
    .proj-card:hover {
        transform: translateY(-6px) rotate(-0.4deg);
        border-color: rgba(255,255,255,0.2) !important;
        box-shadow: 0 20px 50px rgba(0,0,0,0.4);
    }
    @media (max-width: 900px) {
        #projects-grid { grid-template-columns: 1fr 1fr !important; }
        .proj-item { margin-top: 0 !important; }
    }
    @media (max-width: 640px) {
        #projects-grid { grid-template-columns: 1fr !important; }
        #projects-grid-extra { grid-template-columns: 1fr !important; }
        #projects > div { padding-left: 1.25rem !important; padding-right: 1.25rem !important; }
    }
</style>
@endif
