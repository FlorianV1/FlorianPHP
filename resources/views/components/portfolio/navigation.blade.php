@props([
    'profile',
    'navbarLinks' => [],
])

@php
    use App\Models\Settings;
    use App\Support\SiteBranding;

    $brandPrefix = SiteBranding::get('logo_prefix');
    $brandText = Settings::get('navbar_brand_text') ?: SiteBranding::get('logo_text');

    $links = collect($navbarLinks ?: Settings::get('navbar_links', []))
        ->filter(fn ($link) => ($link['enabled'] ?? true))
        ->values();

    $statusText = $profile->status_text ?? 'available for projects';
@endphp

<nav class="site-nav" aria-label="Main">
    <div class="site-nav__inner">

        {{-- Left: brand --}}
        <a href="#top" class="site-nav__brand">
            <span class="site-nav__brand-prefix">{{ $brandPrefix }}</span>{{ $brandText }}
        </a>

        {{-- Center: nav links --}}
        <div class="site-nav__links hidden md:flex">
            @foreach($links as $link)
                <a href="{{ $link['url'] ?? '#' }}" class="nav-link">{{ strtolower($link['label'] ?? '') }}</a>
            @endforeach
        </div>

        {{-- Right: availability pill --}}
        <div class="hidden md:block">
            @if($profile && $profile->status_available)
                <div class="site-nav__pill">
                    <span class="nav-dot"></span>
                    <span class="site-nav__status">{{ $statusText }}</span>
                </div>
            @endif
        </div>

        {{-- Mobile hamburger --}}
        <button id="nav-toggle"
                class="site-nav__toggle md:hidden"
                aria-label="Open menu"
                aria-controls="nav-mobile"
                aria-expanded="false">
            <span id="nbar1" class="site-nav__bar"></span>
            <span id="nbar2" class="site-nav__bar"></span>
            <span id="nbar3" class="site-nav__bar"></span>
        </button>
    </div>

    {{-- Mobile menu — a second copy of the links and the availability badge.
         Hidden from assistive tech while closed so screen readers don't hear
         the navigation twice, and exposed again when it is actually open. --}}
    <div id="nav-mobile" class="site-nav__mobile" aria-hidden="true">
        @foreach($links as $link)
            <a href="{{ $link['url'] ?? '#' }}" class="nav-link mobile-nav-link" tabindex="-1">{{ strtolower($link['label'] ?? '') }}</a>
        @endforeach
        @if($profile && $profile->status_available)
            <div class="site-nav__mobile-status">
                <span class="nav-dot"></span>
                <span class="site-nav__status">{{ $statusText }}</span>
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

        // Keep the duplicate out of the accessibility tree while it is closed,
        // and make its links unreachable by keyboard at the same time.
        menu.setAttribute('aria-hidden', open ? 'false' : 'true');
        menu.querySelectorAll('.mobile-nav-link').forEach(l => {
            if (open) { l.removeAttribute('tabindex'); } else { l.setAttribute('tabindex', '-1'); }
        });

        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');

        b1.style.transform = open ? 'translateY(6px) rotate(45deg)' : '';
        b2.style.opacity   = open ? '0' : '1';
        b3.style.transform = open ? 'translateY(-6px) rotate(-45deg)' : '';
    }

    toggle.addEventListener('click', () => setOpen(!open));
    document.querySelectorAll('.mobile-nav-link').forEach(l => l.addEventListener('click', () => setOpen(false)));
})();
</script>
