@props([
    'profile',
    'navbarLinks' => [],
])

@php
    use App\Models\Settings;

    $brandText = Settings::get('navbar_brand_text', $profile->name ?? 'florian.dev');

    $links = collect($navbarLinks ?: Settings::get('navbar_links', []))
        ->filter(fn ($link) => ($link['enabled'] ?? true))
        ->values();
@endphp

<style>
    @keyframes navDotPulse {
        0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(134,239,172,0); }
        50%       { opacity: 0.7; box-shadow: 0 0 0 5px rgba(134,239,172,0.12); }
    }
    .nav-dot { animation: navDotPulse 2.5s ease-in-out infinite; }
    .nav-link {
        font-family: 'JetBrains Mono', monospace;
        font-size: 12px;
        color: rgba(255,255,255,0.45);
        text-decoration: none;
        letter-spacing: 0.06em;
        transition: color 0.2s;
    }
    .nav-link:hover { color: rgba(255,255,255,1); }
</style>

<nav style="position:fixed;top:0;left:0;right:0;z-index:50;background:rgba(11,11,13,0.88);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-bottom:1px solid rgba(255,255,255,0.07);">
    <div style="max-width:1152px;margin:0 auto;padding:0 2.5rem;height:56px;display:flex;align-items:center;justify-content:space-between;">

        {{-- Left: brand --}}
        <a href="#top" style="font-family:'JetBrains Mono',monospace;font-size:14px;font-weight:600;color:#f1f5f9;text-decoration:none;letter-spacing:-0.01em;">
            <span style="opacity:0.2;">~/</span>{{ $brandText }}
        </a>

        {{-- Center: nav links --}}
        <div class="hidden md:flex" style="gap:2.25rem;">
            @foreach($links as $link)
                <a href="{{ $link['url'] ?? '#' }}" class="nav-link">{{ strtolower($link['label'] ?? '') }}</a>
            @endforeach
        </div>

        {{-- Right: availability pill --}}
        <div class="hidden md:block">
            @if($profile && $profile->status_available)
                <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border:1px solid rgba(255,255,255,0.12);border-radius:4px;">
                    <span class="nav-dot" style="width:7px;height:7px;border-radius:50%;background:#86efac;flex-shrink:0;display:inline-block;"></span>
                    <span style="font-family:'JetBrains Mono',monospace;font-size:11px;color:rgba(255,255,255,0.3);letter-spacing:0.02em;">{{ $profile->status_text ?? 'available for projects' }}</span>
                </div>
            @endif
        </div>

        {{-- Mobile hamburger --}}
        <button id="nav-toggle" class="md:hidden" style="background:none;border:none;cursor:pointer;padding:4px;display:flex;flex-direction:column;gap:5px;" aria-label="Open menu">
            <span id="nbar1" style="display:block;width:20px;height:1px;background:rgba(255,255,255,0.4);transition:all 0.3s;"></span>
            <span id="nbar2" style="display:block;width:20px;height:1px;background:rgba(255,255,255,0.4);transition:all 0.3s;"></span>
            <span id="nbar3" style="display:block;width:20px;height:1px;background:rgba(255,255,255,0.4);transition:all 0.3s;"></span>
        </button>
    </div>

    {{-- Mobile menu --}}
    <div id="nav-mobile" style="display:none;flex-direction:column;padding:1.25rem 2.5rem;gap:1.25rem;border-top:1px solid rgba(255,255,255,0.07);background:rgba(11,11,13,0.98);">
        @foreach($links as $link)
            <a href="{{ $link['url'] ?? '#' }}" class="nav-link mobile-nav-link" style="font-size:13px;">{{ strtolower($link['label'] ?? '') }}</a>
        @endforeach
        @if($profile && $profile->status_available)
            <div style="display:flex;align-items:center;gap:8px;padding-top:1rem;border-top:1px solid rgba(255,255,255,0.07);">
                <span class="nav-dot" style="width:7px;height:7px;border-radius:50%;background:#86efac;display:inline-block;"></span>
                <span style="font-family:'JetBrains Mono',monospace;font-size:11px;color:rgba(255,255,255,0.3);">{{ $profile->status_text ?? 'available for projects' }}</span>
            </div>
        @endif
    </div>
</nav>

<script>
(function () {
    const toggle = document.getElementById('nav-toggle');
    const menu   = document.getElementById('nav-mobile');
    const b1 = document.getElementById('nbar1');
    const b2 = document.getElementById('nbar2');
    const b3 = document.getElementById('nbar3');
    let open = false;

    function setOpen(v) {
        open = v;
        menu.style.display = open ? 'flex' : 'none';
        b1.style.transform = open ? 'translateY(6px) rotate(45deg)' : '';
        b2.style.opacity   = open ? '0' : '1';
        b3.style.transform = open ? 'translateY(-6px) rotate(-45deg)' : '';
    }

    toggle.addEventListener('click', () => setOpen(!open));
    document.querySelectorAll('.mobile-nav-link').forEach(l => l.addEventListener('click', () => setOpen(false)));
})();
</script>
