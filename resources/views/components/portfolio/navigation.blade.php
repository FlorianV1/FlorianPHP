@props(['profile'])

<nav class="fixed top-0 w-full z-50 border-b border-white/5 bg-app-bg/80 backdrop-blur-sm">
    <div class="max-w-6xl mx-auto px-6 py-4">
        <div class="flex justify-between items-center">
            <a href="#" class="text-lg font-semibold text-text-primary">{{ $profile->name ?? 'Name' }}</a>
            <div class="hidden md:flex gap-8 text-sm">
                <a href="#projects" class="text-text-secondary hover:text-accent transition-colors">Projects</a>
                <a href="#experience" class="text-text-secondary hover:text-accent transition-colors">Experience</a>
                <a href="#about" class="text-text-secondary hover:text-accent transition-colors">About</a>
                <a href="#contact" class="text-text-secondary hover:text-accent transition-colors">Contact</a>
            </div>
        </div>
    </div>
</nav>
