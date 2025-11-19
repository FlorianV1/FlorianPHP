@props(['profile'])

<footer class="py-12 px-6 border-t border-white/5">
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="text-text-muted text-sm">© {{ date('Y') }} {{ $profile->name ?? 'Developer' }}. Built with Laravel & Tailwind CSS.</p>
        @if($profile && $profile->social_links)
            <div class="flex gap-6">
                @foreach($profile->social_links as $social)
                    <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" class="text-text-muted hover:text-accent transition-colors text-sm">
                        {{ $social['platform'] }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</footer>
