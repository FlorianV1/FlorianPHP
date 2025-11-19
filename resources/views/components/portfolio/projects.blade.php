@props(['projects'])

@if ($projects->count() > 0)
    <section id="projects" class="py-24 px-6 bg-surface">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold mb-12 text-text-primary">Projects</h2>

            <div class="grid md:grid-cols-2 gap-6">
                @foreach ($projects as $project)
                    <x-portfolio.project-card :project="$project" />
                @endforeach
            </div>
        </div>
    </section>
@endif
