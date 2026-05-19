@props(['items'])

@if($items->count() > 0)
<section id="now" style="background:#0b0b0d;padding:4rem 0 5rem;">
    <div style="max-width:1152px;margin:0 auto;padding:0 2.5rem;">
        <div style="display:flex;align-items:baseline;gap:0.75rem;margin-bottom:2rem;">
            <h2 style="font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(28px,4vw,44px);letter-spacing:-0.03em;margin:0;color:#f1f5f9;">Now</h2>
            <span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:rgba(255,255,255,0.18);margin-bottom:4px;letter-spacing:0.04em;">/ current focus</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:0.75rem;">
            @foreach($items as $item)
                <div style="display:flex;gap:1rem;padding:1rem 1.25rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;">
                    <span style="color:rgba(255,255,255,0.3);flex-shrink:0;margin-top:1px;">→</span>
                    <p style="font-family:'JetBrains Mono',monospace;font-size:13px;color:rgba(255,255,255,0.5);line-height:1.65;margin:0;">{{ $item->description }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
