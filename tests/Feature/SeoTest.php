<?php

use App\Models\Profile;
use App\Models\Project;
use App\Models\Settings;
use App\Support\SiteBranding;

beforeEach(function () {
    Profile::create([
        'name' => 'Florian',
        'role' => 'Software Developer',
        'tagline' => 'I build web applications for small businesses.',
        'subtitle' => 'Web dev',
        'email' => 'florian@example.com',
        'about_text' => 'About me.',
        'location' => 'Netherlands',
        'social_links' => [
            ['platform' => 'GitHub', 'url' => 'https://github.com/example'],
            ['platform' => 'LinkedIn', 'url' => 'https://linkedin.com/in/example'],
        ],
    ]);
});

it('emits canonical, og and twitter tags on the homepage', function () {
    $this->get('/')
        ->assertSee('<link rel="canonical" href="' . route('home') . '">', false)
        ->assertSee('property="og:url" content="' . route('home') . '"', false)
        ->assertSee('property="og:type" content="website"', false)
        ->assertSee('name="twitter:card"', false)
        ->assertSee('I build web applications for small businesses.', false);
});

it('emits a JSON-LD Person block with sameAs profiles', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('application/ld+json');

    preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $html, $m);
    $data = json_decode($m[1], true);

    expect($data['@type'])->toBe('Person')
        ->and($data['jobTitle'])->toBe('Software Developer')
        ->and($data['url'])->toBe(route('home'))
        ->and($data['sameAs'])->toContain('https://github.com/example')
        ->and($data['sameAs'])->toContain('https://linkedin.com/in/example');
});

it('uses the configured full name and title from site branding', function () {
    Settings::set(SiteBranding::KEY, array_merge(SiteBranding::defaults(), [
        'full_name' => 'Florian Geense',
        'site_title' => 'Florian Geense — Laravel developer',
    ]));

    $this->get('/')
        ->assertSee('<title>Florian Geense — Laravel developer</title>', false)
        ->assertSee('Hi, I\'m</span>', false)
        ->assertSee('Florian Geense');
});

it('uses the branded terminal prompt', function () {
    Settings::set(SiteBranding::KEY, array_merge(SiteBranding::defaults(), [
        'terminal_user' => 'florian',
        'terminal_host' => 'florianphp',
        'terminal_path' => '~/work',
    ]));

    $this->get('/')
        ->assertSee('florian@florianphp', false)
        ->assertSee('~/work', false)
        ->assertDontSee('florian@dev');
});

describe('sitemap', function () {
    it('serves xml', function () {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml');
    });

    it('lists the homepage', function () {
        $this->get('/sitemap.xml')->assertSee(route('home'), false);
    });

    it('lists case studies but not projects without one', function () {
        $withCase = Project::create([
            'title' => 'With Case Study', 'description' => 'a', 'is_posted' => true,
            'tech_stack' => [], 'case_study_body' => '<p>Body</p>',
        ]);
        $withoutCase = Project::create([
            'title' => 'No Case Study', 'description' => 'b', 'is_posted' => true, 'tech_stack' => [],
        ]);
        $unposted = Project::create([
            'title' => 'Unposted', 'description' => 'c', 'is_posted' => false,
            'tech_stack' => [], 'case_study_body' => '<p>Body</p>',
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertSee(route('case-study', $withCase), false);
        $response->assertDontSee(route('case-study', $withoutCase), false);
        $response->assertDontSee(route('case-study', $unposted), false);
    });
});

it('serves a robots.txt that points at the sitemap', function () {
    $robots = file_get_contents(public_path('robots.txt'));

    expect($robots)->toContain('Sitemap:')
        ->and($robots)->toContain('sitemap.xml');
});
