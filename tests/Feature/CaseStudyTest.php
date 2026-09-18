<?php

use App\Models\Profile;
use App\Models\Project;
use App\Models\Testimonial;

beforeEach(function () {
    Profile::create([
        'name' => 'Florian',
        'role' => 'Software Developer',
        'tagline' => 'I build web applications.',
        'subtitle' => 'Web dev',
        'email' => 'florian@example.com',
        'about_text' => 'About me.',
    ]);
});

function project(array $overrides = []): Project
{
    return Project::create(array_merge([
        'title' => 'Roadtrip Events',
        'description' => 'A booking platform.',
        'outcome' => 'Replaced a manual booking process.',
        'role' => 'Solo Developer',
        'is_posted' => true,
        'tech_stack' => ['Laravel'],
        'case_study_body' => '<p>How it was built.</p>',
    ], $overrides));
}

it('generates a slug from the title', function () {
    expect(project()->slug)->toBe('roadtrip-events');
});

it('de-duplicates slugs', function () {
    project();
    $second = project(['title' => 'Roadtrip Events']);

    expect($second->slug)->toBe('roadtrip-events-2');
});

it('renders a case study for a posted project with a body', function () {
    $project = project();

    $this->get(route('case-study', $project))
        ->assertOk()
        ->assertSee('Roadtrip Events')
        ->assertSee('How it was built.', false)
        ->assertSee('Replaced a manual booking process.')
        ->assertSee('Solo Developer');
});

it('404s when the project has no case study body', function () {
    $project = project(['case_study_body' => null]);

    $this->get(route('case-study', $project))->assertNotFound();
});

it('404s when the project is not posted', function () {
    $project = project(['is_posted' => false]);

    $this->get(route('case-study', $project))->assertNotFound();
});

it('404s for an unknown slug', function () {
    $this->get('/work/does-not-exist')->assertNotFound();
});

it('gives the case study its own title, description and canonical', function () {
    $project = project([
        'meta_title' => 'Roadtrip Events — a booking platform',
        'meta_description' => 'How a manual booking process became self-service.',
    ]);

    $this->get(route('case-study', $project))
        ->assertSee('<title>Roadtrip Events — a booking platform</title>', false)
        ->assertSee('How a manual booking process became self-service.', false)
        ->assertSee('<link rel="canonical" href="' . route('case-study', $project) . '">', false)
        ->assertSee('property="og:type" content="article"', false);
});

it('falls back to the project title and outcome when no meta is set', function () {
    $project = project();

    $this->get(route('case-study', $project))
        ->assertSee('<title>Roadtrip Events — case study</title>', false)
        ->assertSee('Replaced a manual booking process.', false);
});

it('shows testimonials attached to the project', function () {
    $project = project();

    Testimonial::create([
        'quote' => 'They shipped exactly what we needed.',
        'author_name' => 'Jane Client',
        'company' => 'Roadtrip Events',
        'project_id' => $project->id,
    ]);

    $this->get(route('case-study', $project))
        ->assertSee('They shipped exactly what we needed.', false)
        ->assertSee('Jane Client');
});

it('links to the case study from the project card', function () {
    $project = project();

    $this->get('/')
        ->assertSee(route('case-study', $project), false)
        ->assertSee('Read case study');
});

it('does not link to a case study when there is no body', function () {
    project(['case_study_body' => null]);

    $this->get('/')->assertDontSee('Read case study');
});
