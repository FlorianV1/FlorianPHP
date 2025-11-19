<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $profile->name ?? 'Developer' }} - {{ $profile->role ?? 'Software Developer' }}">
    <meta property="og:title" content="{{ $profile->name ?? 'Developer' }} - {{ $profile->role ?? 'Software Developer' }}">
    <meta property="og:description" content="{{ $profile->tagline ?? 'Full-stack developer' }}">
    <title>{{ $profile->name ?? 'Developer' }} - {{ $profile->role ?? 'Software Developer' }}</title>

    {{-- Devicon for tech logos --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/devicons/devicon@v2.15.1/devicon.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-XXXXXXXXXX');
    </script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'app-bg': '#0E0E10',
                        'surface': '#111418',
                        'accent': '#4A9FFF',
                        'accent-hover': '#2D7CE8',
                        'text-primary': '#E7EAF0',
                        'text-secondary': '#A8ACB3',
                        'text-muted': '#6F737A',
                        'success': '#42D881',
                        'error': '#E04F4F',
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
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0E0E10;
            color: #E7EAF0;
        }
        .fade-in {
            animation: fadeIn 0.6s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        @keyframes fadeIn {
            to { opacity: 1; transform: translateY(0); }
        }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
    </style>
</head>
<body class="antialiased">
{{ $slot }}
</body>
</html>
