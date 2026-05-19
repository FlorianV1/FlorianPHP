@props([
    'profile',
    'navbarLinks' => [],
])

@php
    use App\Models\Settings;

    $brandText  = Settings::get('navbar_brand_text', $profile->name ?? 'florian.dev');
    $brandColor = Settings::get('navbar_brand_color', '#ffffff');

    $links = collect($navbarLinks ?: Settings::get('navbar_links', []))
        ->filter(fn ($link) => ($link['enabled'] ?? true))
        ->values();
@endphp

<nav class="fixed top-0 w-full z-50 border-b border-white/5 bg-app-bg/80 backdrop-blur-md">
    <div class="max-w-6xl mx-auto px-6 py-4">
        {{-- Desktop: 3-column grid --}}
        <div class="hidden md:grid grid-cols-3 items-center">
            {{-- Left: brand --}}
            <a href="#top" class="font-mono text-sm text-white/40 hover:text-white/70 transition-colors tracking-tight">
                ~/{{ $brandText }}
            </a>

            {{-- Center: nav links --}}
            <div class="flex justify-center gap-8">
                @foreach($links as $link)
                    <a href="{{ $link['url'] ?? '#' }}"
                       class="font-mono text-sm text-white/35 hover:text-white/75 transition-colors tracking-tight">
                        {{ strtolower($link['label'] ?? '') }}
                    </a>
                @endforeach
            </div>

            {{-- Right: availability pill --}}
            <div class="flex justify-end">
                @if($profile && $profile->status_available)
                    <div class="flex items-center gap-2 px-3 py-1.5 border border-white/10 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse flex-shrink-0"></span>
                        <span class="font-mono text-xs text-white/40">{{ $profile->status_text ?? 'available for projects' }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Mobile: brand + hamburger --}}
        <div class="flex md:hidden justify-between items-center">
            <a href="#top" class="font-mono text-sm text-white/40">
                ~/{{ $brandText }}
            </a>

            <button
                id="mobile-menu-toggle"
                class="flex flex-col gap-1.5 p-1 text-white/40 hover:text-white/70 transition-colors"
                aria-label="Open menu"
                aria-expanded="false"
            >
                <span class="block w-5 h-px bg-current transition-all duration-300" id="bar1"></span>
                <span class="block w-5 h-px bg-current transition-all duration-300" id="bar2"></span>
                <span class="block w-5 h-px bg-current transition-all duration-300" id="bar3"></span>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div
        id="mobile-menu"
        class="md:hidden hidden flex-col border-t border-white/5 bg-app-bg/95 backdrop-blur-sm px-6 py-5 gap-5"
    >
        @foreach($links as $link)
            <a href="{{ $link['url'] ?? '#' }}"
               class="mobile-menu-link font-mono text-sm text-white/40 hover:text-white/70 transition-colors py-1">
                {{ strtolower($link['label'] ?? '') }}
            </a>
        @endforeach

        @if($profile && $profile->status_available)
            <div class="flex items-center gap-2 pt-4 border-t border-white/5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse flex-shrink-0"></span>
                <span class="font-mono text-xs text-white/35">{{ $profile->status_text ?? 'available for projects' }}</span>
            </div>
        @endif
    </div>
</nav>

<script>
    (function () {
        const toggle = document.getElementById('mobile-menu-toggle');
        const menu   = document.getElementById('mobile-menu');
        const bar1   = document.getElementById('bar1');
        const bar2   = document.getElementById('bar2');
        const bar3   = document.getElementById('bar3');

        let open = false;

        function setOpen(val) {
            open = val;
            toggle.setAttribute('aria-expanded', String(open));

            if (open) {
                menu.classList.remove('hidden');
                menu.classList.add('flex');
                bar1.style.transform = 'translateY(6px) rotate(45deg)';
                bar2.style.opacity   = '0';
                bar3.style.transform = 'translateY(-6px) rotate(-45deg)';
            } else {
                menu.classList.add('hidden');
                menu.classList.remove('flex');
                bar1.style.transform = '';
                bar2.style.opacity   = '1';
                bar3.style.transform = '';
            }
        }

        toggle.addEventListener('click', () => setOpen(!open));

        document.querySelectorAll('.mobile-menu-link').forEach(link => {
            link.addEventListener('click', () => setOpen(false));
        });
    })();
</script>
