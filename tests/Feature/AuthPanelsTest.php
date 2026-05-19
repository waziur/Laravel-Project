<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('new accounts are logged into the user dashboard', function () {
    $response = $this->post('/register', [
        'name' => 'New Client',
        'email' => 'client@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('user.dashboard'));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'client@example.com',
        'is_admin' => false,
    ]);

    $this->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('Logged in as user');
});

test('new accounts return to the intended quote page after registration', function () {
    $response = $this
        ->withSession(['url.intended' => route('quote')])
        ->post('/register', [
            'name' => 'Quote Client',
            'email' => 'quote-client@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertRedirect(route('quote'));

    $this->assertAuthenticated();
});

test('admin accounts are redirected to the admin dashboard after login', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $response
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('status', 'Login successful.');

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Logged in as admin');
});

test('direct database boolean controls admin access', function () {
    $user = User::factory()->create([
        'email' => 'direct-admin@example.com',
        'is_admin' => false,
    ]);

    DB::table('users')
        ->where('id', $user->id)
        ->update(['is_admin' => true]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));
});

test('regular users cannot access admin routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('user.dashboard'));
});

test('logout shows a success message', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Logout successful.');

    $this->assertGuest();
});
