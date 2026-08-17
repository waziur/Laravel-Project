<?php

use App\Models\Booking;
use App\Models\Service;
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
    ['/booking', 'Book A Service'],
    ['/contact', 'Contact Us'],
]);

test('guest users can view the booking page but must authenticate before booking', function () {
    $this->get('/booking')
        ->assertOk()
        ->assertSee('Book A Service')
        ->assertSee('Login required for booking')
        ->assertDontSee('name="service"', false);
});

test('guest users cannot submit bookings', function () {
    $this->post('/booking', [
        'service' => 'Web Development',
        'preferred_date' => now()->addDay()->toDateString(),
        'message' => 'I need a Laravel website.',
    ])
        ->assertRedirect(route('login', ['redirect' => url('/booking')]))
        ->assertSessionHas('url.intended', url('/booking'));

    $this->assertDatabaseCount('bookings', 0);
});

test('authenticated users can view the booking form', function () {
    $user = User::factory()->create([
        'name' => 'Account Holder',
        'email' => 'account@example.com',
    ]);

    $this->actingAs($user)
        ->get('/booking')
        ->assertOk()
        ->assertSee('Book A Service')
        ->assertSee('name="name"', false)
        ->assertSee('name="email"', false)
        ->assertSee('value="Account Holder"', false)
        ->assertSee('value="account@example.com"', false)
        ->assertSee('name="service"', false)
        ->assertSee('name="preferred_time"', false)
        ->assertSee('data-booking-time-picker', false)
        ->assertSee('data-booking-time-value="09:00 AM"', false)
        ->assertSee('data-booking-time-value="09:15 AM"', false)
        ->assertSee('data-booking-time-value="06:00 PM"', false);
});

test('guest users see embedded booking login prompts on public pages', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Book A Service')
        ->assertSee('Login required for booking')
        ->assertDontSee('name="service"', false);
});

test('active service detail pages show full service information', function () {
    $service = Service::where('title', 'Web Development')->firstOrFail();

    $this->get(route('service.show', $service))
        ->assertOk()
        ->assertSee('Web Development')
        ->assertSee('Specific services included in Web Development')
        ->assertSee('Laravel application modules')
        ->assertSee('How this service is completed');
});

test('booking form accepts valid requests from authenticated users', function () {
    $user = User::factory()->create([
        'name' => 'Demo User',
        'email' => 'demo@example.com',
    ]);

    $this->actingAs($user)->post('/booking', [
        'name' => 'Project Booker',
        'email' => 'booker@example.com',
        'phone' => '+8801712345678',
        'service' => 'Web Development',
        'preferred_date' => now()->addDay()->toDateString(),
        'preferred_time' => '10:30 AM',
        'message' => 'I need a Laravel website booking.',
    ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success')
        ->assertRedirect();

    $this->assertDatabaseHas('bookings', [
        'user_id' => $user->id,
        'name' => 'Project Booker',
        'email' => 'booker@example.com',
        'service' => 'Web Development',
        'status' => Booking::STATUS_PENDING,
    ]);
});

test('booking form rejects incomplete or invalid requests', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/booking')
        ->post('/booking', [
            'service' => 'Unknown Service',
            'preferred_date' => now()->subDay()->toDateString(),
            'message' => 'Too short',
        ])
        ->assertRedirect('/booking')
        ->assertSessionHasErrors(['name', 'email', 'service', 'preferred_date', 'message']);

    $this->assertDatabaseCount('bookings', 0);
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
