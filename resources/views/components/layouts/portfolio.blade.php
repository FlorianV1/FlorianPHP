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
    'skills' => null,
])

@php
    use App\Models\Settings;
    use Illuminate\Support\Facades\Storage;

    $favicon = Settings::get('favicon');

    $colors = array_merge([
        'app_bg'         => '#0E0E10',
        'surface'        => '#111418',
        'accent'         => '#4A9FFF',
        'accent_hover'   => '#2D7CE8',
        'text_primary'   => '#E7EAF0',
        'text_secondary' => '#A8ACB3',
        'text_muted'     => '#6F737A',
    ], $colors ?? []);
@endphp

    <!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $profile->name ?? 'Florian' }} - {{ $profile->role ?? 'Software Developer' }}</title>
    <meta name="description" content="{{ $profile->tagline ?? '' }}">
    <meta property="og:title" content="{{ $profile->name ?? 'Florian' }} - {{ $profile->role ?? 'Software Developer' }}">
    <meta property="og:description" content="{{ $profile->tagline ?? '' }}">

    {{-- Favicon from settings --}}
    @if($favicon)
        <link rel="icon" type="image/png" href="{{ Storage::url($favicon) }}">
    @endif

    {{-- Devicons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/devicons/devicon@v2.15.1/devicon.min.css">

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet"
    >

    {{-- Tailwind config with CSS variables --}}
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'app-bg': 'var(--app-bg)',
                        'surface': 'var(--surface)',
                        'accent': 'var(--accent)',
                        'accent-hover': 'var(--accent-hover)',
                        'text-primary': 'var(--text-primary)',
                        'text-secondary': 'var(--text-secondary)',
                        'text-muted': 'var(--text-muted)',
                        success: '#42D881',
                        error: '#E04F4F',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                }
            }
        }
    </script>

    <style>
        :root {
            --app-bg: {{ $colors['app_bg'] }};
            --surface: {{ $colors['surface'] }};
            --accent: {{ $colors['accent'] }};
            --accent-hover: {{ $colors['accent_hover'] }};
            --text-primary: {{ $colors['text_primary'] }};
            --text-secondary: {{ $colors['text_secondary'] }};
            --text-muted: {{ $colors['text_muted'] }};
        }

        body {
            background-color: var(--app-bg);
            color: var(--text-primary);
            font-family: Inter, sans-serif;
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeIn {
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="antialiased"
      id="top"
      data-overlay="{{ $overlay }}"
      data-overlay-intensity="{{ $overlayIntensity }}">

{{-- NAVBAR (reads brand + colors from Settings, links from prop/settings) --}}
<x-portfolio.navigation
    :profile="$profile"
    :navbarLinks="$navbarLinks"
/>

{{-- DYNAMIC SECTIONS VIA COMPONENTS --}}
<main class="space-y-32 pb-16 pt-24">
    @foreach($sectionsOrder ?? [] as $section)
        @php
            $key = $section['section'] ?? null;
            $enabled = $section['enabled'] ?? false;
        @endphp

        @if(! $enabled || ! $key)
            @continue
        @endif

        @if($key === 'hero')
            <x-portfolio.hero :profile="$profile" />

        @elseif($key === 'now')
            <x-portfolio.now :items="$nowItems" />

        @elseif($key === 'projects')
            <x-portfolio.projects :projects="$projects" />

        @elseif($key === 'experience')
            <x-portfolio.experience :experiences="$experiences" />

        @elseif($key === 'skills')
            <x-portfolio.skills-marquee :skills="$skills" />

        @elseif($key === 'about')
            <x-portfolio.about :profile="$profile" />

        @elseif($key === 'contact')
            <x-portfolio.contact :profile="$profile" />
        @endif
    @endforeach
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
            layer.style.position = 'fixed';
            layer.style.pointerEvents = 'none';
            layer.style.inset = '0';
            layer.style.zIndex = '50';
            layer.style.backgroundImage = 'url(/overlays/snow.gif)';
            layer.style.backgroundSize = 'cover';
            layer.style.opacity = String(Math.min(Math.max(intensity / 100, 0.1), 1));
            document.body.appendChild(layer);
        }
    });
</script>
</body>
</html>
