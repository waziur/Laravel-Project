<?php

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('submitted bookings stay pending until an admin changes status', function () {
    $user = User::factory()->create([
        'name' => 'Booking Client',
        'email' => 'client@example.com',
    ]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('booking.store'), [
            'phone' => '+8801712345678',
            'service' => 'Web Development',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '2:00 PM',
            'message' => 'I need a public booking status workflow.',
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    $booking = Booking::where('email', 'client@example.com')->firstOrFail();

    expect($booking->status)->toBe(Booking::STATUS_PENDING);

    $this->actingAs($admin)
        ->get(route('admin.bookings'))
        ->assertOk()
        ->assertSee('Booking Client')
        ->assertSee('Web Development')
        ->assertSee('Pending');

    $this->actingAs($admin)
        ->patch(route('admin.bookings.status', $booking), [
            'status' => Booking::STATUS_ACCEPTED,
        ])
        ->assertRedirect(route('admin.bookings'))
        ->assertSessionHas('status', 'Booking status updated to accepted.');

    $this->assertDatabaseHas('bookings', [
        'id' => $booking->id,
        'status' => Booking::STATUS_ACCEPTED,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.status', $booking), [
            'status' => Booking::STATUS_REJECTED,
        ])
        ->assertRedirect(route('admin.bookings'))
        ->assertSessionHas('status', 'Booking status updated to rejected.');

    $this->assertDatabaseHas('bookings', [
        'id' => $booking->id,
        'status' => Booking::STATUS_REJECTED,
    ]);
});

test('users can only see their own bookings', function () {
    $owner = User::factory()->create([
        'name' => 'Owner User',
        'email' => 'owner@example.com',
    ]);
    $other = User::factory()->create([
        'name' => 'Other User',
        'email' => 'other@example.com',
    ]);

    Booking::create([
        'user_id' => $owner->id,
        'name' => $owner->name,
        'email' => $owner->email,
        'phone' => '+8801711111111',
        'service' => 'Cyber Security',
        'preferred_date' => now()->addDays(2)->toDateString(),
        'preferred_time' => 'Morning',
        'message' => 'Owner booking information should be visible.',
        'status' => Booking::STATUS_ACCEPTED,
    ]);

    Booking::create([
        'user_id' => $other->id,
        'name' => $other->name,
        'email' => $other->email,
        'phone' => '+8801722222222',
        'service' => 'Data Analytics',
        'preferred_date' => now()->addDays(3)->toDateString(),
        'preferred_time' => 'Evening',
        'message' => 'Other user booking should remain hidden.',
        'status' => Booking::STATUS_PENDING,
    ]);

    $this->actingAs($owner)
        ->get(route('user.bookings'))
        ->assertOk()
        ->assertSee('Cyber Security')
        ->assertSee('Owner booking information should be visible.')
        ->assertSee('Accepted')
        ->assertDontSee('Data Analytics')
        ->assertDontSee('Other user booking should remain hidden.');
});
