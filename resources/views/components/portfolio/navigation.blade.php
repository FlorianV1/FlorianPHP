@props([
    'profile',
    'navbarLinks' => [],
])

@php
    use App\Models\Settings;

    $brandText  = Settings::get('navbar_brand_text', $profile->name ?? 'Brand');
    $brandColor = Settings::get('navbar_brand_color', '#ffffff');

    $links = collect($navbarLinks ?: Settings::get('navbar_links', []))
        ->filter(fn ($link) => ($link['enabled'] ?? true))
        ->values();
@endphp

<nav class="fixed top-0 w-full z-50 border-b border-white/5 bg-app-bg/80 backdrop-blur-sm">
    <div class="max-w-6xl mx-auto px-6 py-4">
        <div class="flex justify-between items-center">
            <a href="#top"
               class="text-lg font-bold"
               style="color: {{ $brandColor }};">
                {{ $brandText }}
            </a>

            <div class="hidden md:flex gap-8 text-sm">
                @foreach($links as $link)
                    <a href="{{ $link['url'] ?? '#' }}"
                       class="text-text-secondary hover:text-accent transition-colors">
                        {{ $link['label'] ?? '' }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</nav>
