<?php

use App\Models\Booking;
use App\Models\User;
use App\Services\BookingSchedule;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated users can see which booking times are available', function () {
    $user = User::factory()->create();
    $date = now()->addDay()->toDateString();

    $this->actingAs($user)
        ->post(route('booking.store'), [
            'name' => 'First Client',
            'email' => 'first@example.com',
            'phone' => '+8801711111111',
            'service' => 'Web Development',
            'preferred_date' => $date,
            'preferred_time' => '10:30 AM',
            'message' => 'Reserve this time for the first client.',
        ])
        ->assertSessionHasNoErrors();

    $response = $this->actingAs($user)
        ->getJson(route('booking.availability', ['date' => $date]))
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('date', $date)
        ->assertJsonPath('timezone', config('booking.timezone'));

    $slots = collect($response->json('slots'))->keyBy('value');

    expect($slots['10:30 AM']['available'])->toBeFalse()
        ->and($slots['10:45 AM']['available'])->toBeTrue();
});

test('the same slot cannot be booked twice even for different services', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $date = now()->addDay()->toDateString();
    $payload = [
        'name' => 'First Client',
        'email' => 'first@example.com',
        'phone' => '+8801711111111',
        'service' => 'Web Development',
        'preferred_date' => $date,
        'preferred_time' => '11:00 AM',
        'message' => 'The first customer is requesting this time.',
    ];

    $this->actingAs($firstUser)
        ->post(route('booking.store'), $payload)
        ->assertSessionHasNoErrors();

    $this->actingAs($secondUser)
        ->from(route('booking'))
        ->post(route('booking.store'), array_merge($payload, [
            'name' => 'Second Client',
            'email' => 'second@example.com',
            'service' => 'Cyber Security',
        ]))
        ->assertRedirect(route('booking'))
        ->assertSessionHasErrors(['preferred_time']);

    $this->assertDatabaseCount('bookings', 1);
    $this->assertDatabaseHas('bookings', [
        'user_id' => $firstUser->id,
        'slot_key' => $date.' 11:00',
    ]);
});

test('the database unique index is the final concurrent booking guard', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $date = now()->addDay()->toDateString();
    $attributes = [
        'name' => 'Direct Booking',
        'email' => 'direct@example.com',
        'phone' => null,
        'service' => 'Web Development',
        'preferred_date' => $date,
        'preferred_time' => '11:15 AM',
        'message' => 'This record exercises the database-level slot guard.',
        'status' => Booking::STATUS_PENDING,
    ];

    Booking::create($attributes + ['user_id' => $firstUser->id]);

    expect(
        fn () => Booking::create($attributes + ['user_id' => $secondUser->id])
    )->toThrow(UniqueConstraintViolationException::class);

    $this->assertDatabaseCount('bookings', 1);
});

test('rejecting a booking releases its time for another customer', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $date = now()->addDay()->toDateString();
    $payload = [
        'name' => 'First Client',
        'email' => 'first@example.com',
        'phone' => null,
        'service' => 'Web Development',
        'preferred_date' => $date,
        'preferred_time' => '11:30 AM',
        'message' => 'This booking will be rejected to release its time.',
    ];

    $this->actingAs($firstUser)
        ->post(route('booking.store'), $payload)
        ->assertSessionHasNoErrors();

    $firstBooking = Booking::firstOrFail();

    $this->actingAs($admin)
        ->patch(route('admin.bookings.status', $firstBooking), [
            'status' => Booking::STATUS_REJECTED,
        ])
        ->assertSessionHasNoErrors();

    expect($firstBooking->fresh()->slot_key)->toBeNull();

    $this->actingAs($secondUser)
        ->post(route('booking.store'), array_merge($payload, [
            'name' => 'Second Client',
            'email' => 'second@example.com',
        ]))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('bookings', 2);
    $this->assertDatabaseHas('bookings', [
        'user_id' => $secondUser->id,
        'slot_key' => $date.' 11:30',
    ]);
});

test('completing an accepted booking releases its time for another customer', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $date = now()->addDay()->toDateString();
    $payload = [
        'name' => 'Completed Client',
        'email' => 'completed@example.com',
        'phone' => null,
        'service' => 'Web Development',
        'preferred_date' => $date,
        'preferred_time' => '11:45 AM',
        'message' => 'This accepted booking will be marked as completed.',
    ];

    $this->actingAs($firstUser)
        ->post(route('booking.store'), $payload)
        ->assertSessionHasNoErrors();

    $firstBooking = Booking::firstOrFail();

    $this->actingAs($admin)
        ->patch(route('admin.bookings.status', $firstBooking), [
            'status' => Booking::STATUS_ACCEPTED,
        ])
        ->assertSessionHasNoErrors();

    expect($firstBooking->fresh()->slot_key)->toBe($date.' 11:45');

    $this->actingAs($admin)
        ->patch(route('admin.bookings.status', $firstBooking), [
            'status' => Booking::STATUS_COMPLETED,
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Booking status updated to completed.');

    expect($firstBooking->fresh()->status)->toBe(Booking::STATUS_COMPLETED)
        ->and($firstBooking->fresh()->slot_key)->toBeNull();

    $this->actingAs($secondUser)
        ->post(route('booking.store'), array_merge($payload, [
            'name' => 'Next Client',
            'email' => 'next@example.com',
        ]))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('bookings', [
        'user_id' => $secondUser->id,
        'slot_key' => $date.' 11:45',
    ]);
});

test('an admin cannot reactivate a rejected booking after its time is taken', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $date = now()->addDay()->toDateString();
    $schedule = app(BookingSchedule::class);

    $firstBooking = Booking::create([
        'user_id' => $firstUser->id,
        'name' => 'First Client',
        'email' => 'first@example.com',
        'service' => 'Web Development',
        'preferred_date' => $date,
        'preferred_time' => '12:00 PM',
        'message' => 'The original booking was rejected.',
        'status' => Booking::STATUS_REJECTED,
    ]);

    Booking::create([
        'user_id' => $secondUser->id,
        'name' => 'Second Client',
        'email' => 'second@example.com',
        'service' => 'Cyber Security',
        'preferred_date' => $date,
        'preferred_time' => '12:00 PM',
        'slot_key' => $schedule->slotKey($date, '12:00 PM'),
        'message' => 'The second customer now owns this time.',
        'status' => Booking::STATUS_PENDING,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.bookings'))
        ->patch(route('admin.bookings.status', $firstBooking), [
            'status' => Booking::STATUS_ACCEPTED,
        ])
        ->assertRedirect(route('admin.bookings'))
        ->assertSessionHasErrors(['status']);

    expect($firstBooking->fresh()->status)->toBe(Booking::STATUS_REJECTED)
        ->and($firstBooking->fresh()->slot_key)->toBeNull();
});

test('booking time must be a future configured slot', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('booking'))
        ->post(route('booking.store'), [
            'name' => 'Invalid Slot Client',
            'email' => 'invalid-slot@example.com',
            'service' => 'Web Development',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '10:07 AM',
            'message' => 'This time is not aligned to the booking calendar.',
        ])
        ->assertRedirect(route('booking'))
        ->assertSessionHasErrors(['preferred_time']);

    $this->assertDatabaseCount('bookings', 0);
});
