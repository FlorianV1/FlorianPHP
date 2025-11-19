<section id="hero" class="min-h-screen flex items-center justify-center px-6 pt-20">
    <div class="max-w-4xl w-full">
        <div class="fade-in">
            <p class="text-accent text-sm font-medium mb-4 tracking-wide uppercase">
                {{ $profile->role }}
            </p>
        </div>

        <div class="fade-in delay-100">
            <h1 class="text-5xl md:text-7xl font-bold mb-6 text-text-primary">
                Hi, I'm {{ $profile->name }}.
            </h1>
        </div>

        <div class="fade-in delay-200">
            <p class="text-xl md:text-2xl text-text-secondary mb-4 leading-relaxed">
                {{ $profile->tagline }}
            </p>
        </div>

        <div class="fade-in delay-300">
            <p class="text-lg text-text-muted mb-8">
                {{ $profile->subtitle }}
            </p>
        </div>

        @if ($profile)
            <div class="fade-in delay-400 mt-8">
                <div class="flex items-center gap-2 text-sm text-text-muted">
                    <span class="w-2 h-2 rounded-full {{ $profile->status_available ? 'bg-success animate-pulse' : 'bg-text-muted' }}"></span>
                    <span>{{ $profile->status_text }}</span>
                </div>
            </div>
        @endif
    </div>
</section>
