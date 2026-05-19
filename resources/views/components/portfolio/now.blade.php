@props(['items'])

@if($items->count() > 0)
    <section id="now" class="pt-4 pb-20 px-6">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold mb-8 text-text-primary">What I'm focused on now</h2>
            <div class="grid gap-3">
                @foreach($items as $item)
                    <div class="flex gap-4 px-5 py-4 bg-surface border border-white/5 rounded-xl hover:border-accent/20 transition-colors group">
                        <span class="text-accent mt-0.5 flex-shrink-0 group-hover:translate-x-0.5 transition-transform">→</span>
                        <p class="text-text-secondary">{{ $item->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
