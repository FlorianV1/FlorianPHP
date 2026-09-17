<?php

use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Service;
use App\Models\Stat;
use App\Models\Testimonial;

beforeEach(function () {
    Profile::create([
        'name' => 'Florian',
        'role' => 'Software Developer',
        'tagline' => 'I build web applications.',
        'subtitle' => 'Web dev',
        'email' => 'florian@example.com',
        'about_text' => 'About me.',
        'location' => 'Netherlands',
        'location_timezone' => 'Europe/Amsterdam',
        'social_links' => [
            ['platform' => 'GitHub', 'url' => 'https://github.com/example'],
            ['platform' => 'LinkedIn', 'url' => 'https://linkedin.com/in/example'],
        ],
    ]);
});

it('renders the homepage', function () {
    $this->get('/')->assertOk();
});

it('shows posted projects and hides unposted ones', function () {
    Project::create([
        'title' => 'Visible Project', 'description' => 'Shown.', 'is_posted' => true, 'tech_stack' => [],
    ]);
    Project::create([
        'title' => 'Hidden Project', 'description' => 'Not shown.', 'is_posted' => false, 'tech_stack' => [],
    ]);

    $this->get('/')
        ->assertSee('Visible Project')
        ->assertDontSee('Hidden Project');
});

it('shows a finished project — visibility follows is_posted, not is_ongoing', function () {
    Project::create([
        'title' => 'Finished Project',
        'description' => 'Wrapped up last year.',
        'is_posted' => true,
        'is_ongoing' => false,
        'tech_stack' => [],
    ]);

    $this->get('/')->assertSee('Finished Project');
});

it('renders services, testimonials and education sections', function () {
    Service::create(['title' => 'Custom web apps', 'description' => 'Built for you.']);
    Testimonial::create(['quote' => 'Excellent work.', 'author_name' => 'Jane Client', 'company' => 'Acme']);
    Experience::create([
        'entry_type' => 'education',
        'title' => 'BSc Computer Science',
        'company' => 'Example University',
        'started_at' => '2019-09-01',
        'ended_at' => '2023-07-01',
        'responsibilities' => [],
        'skills' => [],
    ]);

    $this->get('/')
        ->assertSee('Custom web apps')
        ->assertSee('Excellent work.', false)
        ->assertSee('Jane Client')
        ->assertSee('BSc Computer Science')
        ->assertSee('Education');
});

it('keeps education out of the work experience timeline', function () {
    Experience::create([
        'entry_type' => 'work', 'title' => 'Developer', 'company' => 'Work Co',
        'started_at' => '2024-01-01', 'responsibilities' => [], 'skills' => [],
    ]);
    Experience::create([
        'entry_type' => 'education', 'title' => 'A Degree', 'company' => 'School',
        'started_at' => '2019-01-01', 'responsibilities' => [], 'skills' => [],
    ]);

    $html = $this->get('/')->getContent();

    $experienceSection = substr($html, (int) strpos($html, 'id="experience"'), (int) strpos($html, 'id="education"') - (int) strpos($html, 'id="experience"'));

    expect($experienceSection)->toContain('Work Co');
    expect($experienceSection)->not->toContain('A Degree');
});

it('renders stats as integers with the matching singular or plural label', function () {
    Stat::create([
        'value' => 1, 'label_singular' => 'current project', 'label_plural' => 'current projects', 'sort_order' => 1,
    ]);
    Stat::create([
        'value' => 4, 'label_singular' => 'happy client', 'label_plural' => 'happy clients', 'sort_order' => 2,
    ]);

    $this->get('/')
        ->assertSee('current project')
        ->assertDontSee('current projects')
        ->assertSee('happy clients');
});

it('counts posted projects for an auto-sourced stat', function () {
    Project::create(['title' => 'One', 'description' => 'a', 'is_posted' => true, 'tech_stack' => []]);
    Project::create(['title' => 'Two', 'description' => 'b', 'is_posted' => true, 'tech_stack' => []]);
    Project::create(['title' => 'Draft', 'description' => 'c', 'is_posted' => false, 'tech_stack' => []]);

    Stat::create([
        'value' => 99,
        'auto_source' => 'projects_count',
        'label_singular' => 'project',
        'label_plural' => 'projects',
    ]);

    $this->get('/')
        ->assertSee('projects')
        ->assertDontSee('99');
});

it('renders the timezone as an IANA zone with a live abbreviation', function () {
    $expected = 'Europe/Amsterdam (' . now()->setTimezone('Europe/Amsterdam')->format('T') . ')';

    $this->get('/')
        ->assertSee($expected)
        ->assertDontSee('UTC+1 / CET');
});

it('labels footer social links with the platform name rather than the URL', function () {
    $html = $this->get('/')->getContent();

    $footer = substr($html, (int) strpos($html, '<footer'));

    expect($footer)->toContain('site-footer__social-label');
    expect($footer)->toContain('>GitHub<');
    expect($footer)->toContain('>LinkedIn<');
});

it('hides the duplicated mobile nav from assistive tech while it is closed', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('id="nav-mobile" class="site-nav__mobile" aria-hidden="true"');
    expect($html)->toContain('aria-expanded="false"');
});

it('keeps the honeypot hidden from assistive tech and the keyboard', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('class="contact__honeypot" aria-hidden="true"');
    expect($html)->toContain('name="website" tabindex="-1" autocomplete="off"');
});

it('emits a single h1', function () {
    preg_match_all('/<h1[\s>]/', $this->get('/')->getContent(), $matches);

    expect($matches[0])->toHaveCount(1);
});
