@props([
    'profile',
    'colors' => [],
    'overlay' => 'none',
    'overlayIntensity' => 50,
    'sectionsOrder' => [],
    'navbarLinks' => [],
    'nowItems' => null,
    'projects' => null,
    'experiences' => null,
    'education' => null,
    'skills' => null,
    'services' => null,
    'testimonials' => null,
    'stats' => null,
    'seoTitle' => null,
    'seoDescription' => null,
    'seoType' => 'website',
    'seoImage' => null,
    'seoPerson' => null,
])

@php
    use App\Models\Settings;
    use App\Support\SiteBranding;
    use Illuminate\Support\Facades\Storage;

    $favicon = Settings::get('favicon');

    $colors = array_merge([
        'app_bg'         => '#0b0b0d',
        'surface'        => '#111114',
        'accent'         => '#4A9FFF',
        'accent_hover'   => '#2D7CE8',
        'text_primary'   => '#E7EAF0',
        'text_secondary' => '#A8ACB3',
        'text_muted'     => '#6F737A',
    ], $colors ?: []);
@endphp

    <!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <x-portfolio.seo
        :title="$seoTitle ?? SiteBranding::title($profile)"
        :description="$seoDescription ?? SiteBranding::metaDescription($profile)"
        :type="$seoType"
        :image="$seoImage"
        :person="$seoPerson"
    />

    {{-- Bugsnag: only when a browser key is configured --}}
    @if ($bugsnagBrowserKey = config('services.bugsnag.browser_key'))
        <script src="//d2wy8f7a9ursnm.cloudfront.net/v8/bugsnag.min.js"></script>
        <script type="module">
            import BugsnagPerformance from '//d2wy8f7a9ursnm.cloudfront.net/v1/bugsnag-performance.min.js'
            Bugsnag.start({ apiKey: @json($bugsnagBrowserKey) })
            BugsnagPerformance.start({ apiKey: @json($bugsnagBrowserKey) })
        </script>
    @endif

    {{-- Favicon: a CMS upload wins, otherwise the FlorianPHP mark. --}}
    @if($favicon)
        <link rel="icon" type="image/png" href="{{ Storage::url($favicon) }}">
    @else
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/brand/favicon-32.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/brand/icon-192.png') }}">
        <link rel="alternate icon" href="/favicon.ico">
    @endif

    {{-- Home-screen icon: always the mark, never the CMS favicon (which is
         sized for a browser tab and would look rough at 180px). --}}
    <link rel="apple-touch-icon" href="{{ asset('images/brand/apple-touch-icon.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800;900&family=JetBrains+Mono:wght@300;400;500;600&display=swap"
        rel="stylesheet"
    >

    {{-- Compiled stylesheet (Tailwind + portfolio components).
         CSS only — the portfolio ships no bundled JS, and resources/js/app.js
         is just axios, which nothing on this page uses. --}}
    @vite('resources/css/app.css')

    {{-- Palette from Settings — the only styling that has to stay per-request. --}}
    <style>
        :root {
            --app-bg: {{ $colors['app_bg'] }};
            --surface: {{ $colors['surface'] }};
            --accent: {{ $colors['accent'] }};
            --accent-hover: {{ $colors['accent_hover'] }};
            --text-primary: {{ $colors['text_primary'] }};
            --text-secondary: {{ $colors['text_secondary'] }};
            --text-muted: {{ $colors['text_muted'] }};
            --bg: {{ $colors['app_bg'] }};
            --bg2: {{ $colors['surface'] }};
        }
    </style>
</head>

<body class="antialiased"
      id="top"
      data-overlay="{{ $overlay }}"
      data-overlay-intensity="{{ $overlayIntensity }}">

<a href="#main" class="skip-link">Skip to content</a>

{{-- NAVBAR (reads brand + colors from Settings, links from prop/settings) --}}
<x-portfolio.navigation
    :profile="$profile"
    :navbarLinks="$navbarLinks"
/>

<main id="main">
    @if(trim($slot) !== '')
        {{ $slot }}
    @else
        {{-- DYNAMIC SECTIONS VIA COMPONENTS --}}
        @foreach($sectionsOrder ?? [] as $section)
            @php
                $key = $section['section'] ?? null;
                $enabled = $section['enabled'] ?? false;
            @endphp

            @if(! $enabled || ! $key)
                @continue
            @endif

            @if($key === 'hero')
                <x-portfolio.hero :profile="$profile" :skills="$skills" :projects="$projects" />

            @elseif($key === 'services')
                <x-portfolio.services :services="$services" />

            @elseif($key === 'now')
                <x-portfolio.now :items="$nowItems" />

            @elseif($key === 'projects')
                <x-portfolio.projects :projects="$projects" />

            @elseif($key === 'testimonials')
                <x-portfolio.testimonials :testimonials="$testimonials" />

            @elseif($key === 'experience')
                <x-portfolio.experience :experiences="$experiences" />

            @elseif($key === 'education')
                <x-portfolio.education :education="$education" />

            @elseif($key === 'skills')
                <x-portfolio.skills-marquee :skills="$skills" />

            @elseif($key === 'about')
                <x-portfolio.about :profile="$profile" :stats="$stats" />

            @elseif($key === 'contact')
                <x-portfolio.contact :profile="$profile" />
            @endif
        @endforeach
    @endif
</main>

{{-- FOOTER --}}
<x-portfolio.footer :profile="$profile" />

{{-- Overlay engine --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const body = document.body;
        const type = body.dataset.overlay ?? 'none';
        const intensity = parseInt(body.dataset.overlayIntensity ?? '50', 10);

        if (type === 'none') return;

        if (type === 'snow') {
            const layer = document.createElement('div');
            layer.id = 'overlay-snow';
            layer.className = 'overlay-layer';
            layer.style.backgroundImage = 'url(/overlays/snow.gif)';
            layer.style.opacity = String(Math.min(Math.max(intensity / 100, 0.1), 1));
            document.body.appendChild(layer);
        }
    });
</script>
</body>
</html>
