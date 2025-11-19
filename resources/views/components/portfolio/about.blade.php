@props(['profile'])

@if($profile && $profile->about_text)
    <section id="about" class="py-24 px-6">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold mb-8 text-text-primary">About me</h2>
            <div class="prose prose-invert max-w-none text-text-secondary leading-relaxed">
                {!! $profile->about_text !!}
            </div>
        </div>
    </section>
@endif
