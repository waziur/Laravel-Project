<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('frontend pages render successfully', function (string $uri, string $text) {
    $this->get($uri)
        ->assertOk()
        ->assertSee($text);
})->with([
    ['/', 'Digital Solution'],
    ['/about', 'About Us'],
    ['/service', 'Our Services'],
    ['/feature', 'Why Choose Us'],
    ['/team', 'Team Members'],
    ['/testimonial', 'Testimonial'],
    ['/quote', 'Request A Quote'],
    ['/contact', 'Contact Us'],
]);

test('guest users can view the fixed quote form', function () {
    $this->get('/quote')
        ->assertOk()
        ->assertSee('Request A Quote')
        ->assertSee('name="service"', false);
});

test('guest users cannot submit quote requests', function () {
    $this->post('/quote', [
        'name' => 'Demo User',
        'email' => 'demo@example.com',
        'service' => 'Web Development',
        'message' => 'I need a Laravel website.',
    ])
        ->assertRedirect(route('login', ['redirect' => url('/quote')]))
        ->assertSessionHas('url.intended', url('/quote'));

    $this->assertDatabaseCount('quote_requests', 0);
});

test('authenticated users can view the quote form', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/quote')
        ->assertOk()
        ->assertSee('Request A Quote')
        ->assertSee('name="service"', false);
});

test('guest users see embedded quote forms on public pages', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Request A Quote')
        ->assertSee('name="service"', false);
});

test('quote form accepts valid requests', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/quote', [
        'name' => 'Demo User',
        'email' => 'demo@example.com',
        'service' => 'Web Development',
        'message' => 'I need a Laravel website.',
    ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success')
        ->assertRedirect();

    $this->assertDatabaseHas('quote_requests', [
        'email' => 'demo@example.com',
        'service' => 'Web Development',
        'is_approved' => false,
    ]);
});

test('quote form rejects incomplete or invalid requests', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/quote')
        ->post('/quote', [
            'name' => 'A',
            'email' => 'not-an-email',
            'service' => 'Unknown Service',
            'message' => 'Too short',
        ])
        ->assertRedirect('/quote')
        ->assertSessionHasErrors(['name', 'email', 'service', 'message']);

    $this->assertDatabaseCount('quote_requests', 0);
});

test('contact form accepts valid messages', function () {
    $this->post('/contact', [
        'name' => 'Demo User',
        'email' => 'demo@example.com',
        'subject' => 'Project inquiry',
        'message' => 'Please contact me about a project.',
    ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success')
        ->assertRedirect();

    $this->assertDatabaseHas('contact_messages', [
        'email' => 'demo@example.com',
        'subject' => 'Project inquiry',
    ]);
});
