@props(['profile'])

<section class="min-h-screen flex items-center justify-center px-6 pt-20">
    <div class="max-w-4xl w-full">
        <div class="fade-in">
            <p class="text-accent text-sm font-medium mb-4 tracking-wide uppercase">{{ $profile->role ?? 'Job' }}</p>
        </div>
        <div class="fade-in delay-100">
            <h1 class="text-5xl md:text-7xl font-bold mb-6 text-text-primary">Hi, I'm {{ $profile->name ?? 'Name' }}.</h1>
        </div>
        <div class="fade-in delay-200">
            <p class="text-xl md:text-2xl text-text-secondary mb-4 leading-relaxed">
                {{ $profile->tagline ?? 'I build web applications with a focus on reliability, performance, and clear code.' }}
            </p>
        </div>
        <div class="fade-in delay-300">
            <p class="text-lg text-text-muted mb-8">
                {{ $profile->subtitle ?? 'Currently working on modern PHP/Laravel stacks and exploring better ways to ship backend-heavy products.' }}
            </p>
        </div>
        <div class="fade-in delay-400 flex gap-4 flex-wrap">
            <a href="#projects" class="px-6 py-3 bg-accent hover:bg-accent-hover text-white font-medium rounded-xl transition-colors">
                View Projects
            </a>
            <a href="#contact" class="px-6 py-3 border border-white/10 hover:border-accent text-text-primary font-medium rounded-xl transition-colors">
                Contact Me
            </a>
        </div>
        @if($profile)
            <div class="fade-in delay-400 mt-8">
                <div class="flex items-center gap-2 text-sm text-text-muted">
                    <span class="w-2 h-2 rounded-full {{ $profile->status_available ? 'bg-success animate-pulse' : 'bg-text-muted' }}"></span>
                    <span>{{ $profile->status_text }}</span>
                </div>
            </div>
        @endif
    </div>
</section>
