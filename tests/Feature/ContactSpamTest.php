<?php

use App\Mail\ContactNotification;
use App\Models\ContactMessage;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    Profile::query()->delete();
    Profile::create([
        'name'       => 'Florian',
        'role'       => 'Developer',
        'tagline'    => 'Builder',
        'subtitle'   => 'Web dev',
        'email'      => 'florian@example.com',
        'about_text' => 'About me.',
    ]);
});

function submit(array $overrides = [])
{
    $payload = array_merge([
        'name'    => 'Jane Developer',
        'email'   => 'jane@example.com',
        'message' => 'Hi Florian, I really enjoyed your portfolio and would love to collaborate.',
        'website' => '',            // honeypot left blank
        'form_ts' => time() - 10,   // filled the form 10s ago
    ], $overrides);

    return test()->post('/contact', $payload);
}

it('accepts a genuine message and emails the owner', function () {
    submit()->assertRedirect()->assertSessionHas('contact_success');

    expect(ContactMessage::withSpam()->count())->toBe(1);
    expect(ContactMessage::onlySpam()->count())->toBe(0);
    Mail::assertSent(ContactNotification::class);
});

it('quarantines when the honeypot is filled and sends no email', function () {
    submit(['website' => 'http://spammer.example'])
        ->assertRedirect()->assertSessionHas('contact_success');

    expect(ContactMessage::onlySpam()->count())->toBe(1);
    expect(ContactMessage::count())->toBe(0); // hidden by global scope
    Mail::assertNothingSent();
});

it('quarantines instant (too-fast) submissions', function () {
    submit(['form_ts' => time()])->assertSessionHas('contact_success');
    expect(ContactMessage::onlySpam()->count())->toBe(1);
    Mail::assertNothingSent();
});

it('quarantines a missing timestamp', function () {
    submit(['form_ts' => null])->assertSessionHas('contact_success');
    expect(ContactMessage::onlySpam()->count())->toBe(1);
});

it('quarantines messages containing links', function () {
    submit(['message' => 'The $27,000,000 Jackpot Is Open 24/7 for You https://gnosis.link/EbCkE'])
        ->assertSessionHas('contact_success');
    expect(ContactMessage::onlySpam()->count())->toBe(1);
    Mail::assertNothingSent();
});

it('quarantines spam keywords without a link', function () {
    submit(['message' => 'Win a huge jackpot at our casino today!'])
        ->assertSessionHas('contact_success');
    expect(ContactMessage::onlySpam()->count())->toBe(1);
});

it('quarantines when the name is an email address', function () {
    submit(['name' => 'adriennejdaly@gmail.com'])
        ->assertSessionHas('contact_success');
    expect(ContactMessage::onlySpam()->count())->toBe(1);
});

it('still validates required fields', function () {
    test()->post('/contact', ['website' => '', 'form_ts' => time() - 10])
        ->assertSessionHasErrors(['name', 'email', 'message']);
    expect(ContactMessage::withSpam()->count())->toBe(0);
});
