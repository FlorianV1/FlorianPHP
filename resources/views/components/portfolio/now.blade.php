@props(['items'])

@if($items->count() > 0)
    <section id="now" class="py-24 px-6">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold mb-8 text-text-primary">What I'm focused on now</h2>
            <div class="space-y-4">
                @foreach($items as $item)
                    <div class="flex gap-4">
                        <span class="text-accent mt-1">→</span>
                        <p class="text-text-secondary">{{ $item->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
