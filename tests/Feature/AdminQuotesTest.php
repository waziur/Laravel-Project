<?php

use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('submitted quote requests stay pending until an admin approves them', function () {
    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('quote.store'), [
            'name' => 'Demo Client',
            'email' => 'client@example.com',
            'service' => 'Web Development',
            'message' => 'I need a public quote approval workflow.',
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    $quote = QuoteRequest::where('email', 'client@example.com')->firstOrFail();

    expect($quote->is_approved)->toBeFalse();

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('I need a public quote approval workflow.');

    $this->actingAs($admin)
        ->patch(route('admin.quotes.approval', $quote), [
            'is_approved' => '1',
        ])
        ->assertRedirect(route('admin.quotes'))
        ->assertSessionHas('status', 'Quote request approved and now visible on the homepage.');

    $this->assertDatabaseHas('quote_requests', [
        'id' => $quote->id,
        'is_approved' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Demo Client')
        ->assertSee('I need a public quote approval workflow.');
});

test('admin can create update and delete quote requests', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.quotes.store'), [
            'name' => 'Managed Client',
            'email' => 'managed@example.com',
            'service' => 'Cyber Security',
            'message' => 'Please review our security roadmap and implementation budget.',
            'is_approved' => '1',
        ])
        ->assertRedirect(route('admin.quotes'))
        ->assertSessionHas('status', 'Quote request created successfully.');

    $quote = QuoteRequest::where('email', 'managed@example.com')->firstOrFail();

    $this->assertDatabaseHas('quote_requests', [
        'id' => $quote->id,
        'is_approved' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Managed Client');

    $this->actingAs($admin)
        ->put(route('admin.quotes.update', $quote), [
            'name' => 'Managed Client Updated',
            'email' => 'managed-updated@example.com',
            'service' => 'Data Analytics',
            'message' => 'Updated analytics planning request for a dashboard build.',
            'is_approved' => '0',
        ])
        ->assertRedirect(route('admin.quotes'))
        ->assertSessionHas('status', 'Quote request updated successfully.');

    $this->assertDatabaseHas('quote_requests', [
        'id' => $quote->id,
        'email' => 'managed-updated@example.com',
        'is_approved' => false,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Managed Client Updated');

    $this->actingAs($admin)
        ->delete(route('admin.quotes.destroy', $quote))
        ->assertRedirect(route('admin.quotes'))
        ->assertSessionHas('status', 'Quote request deleted successfully.');

    $this->assertDatabaseMissing('quote_requests', [
        'id' => $quote->id,
    ]);
});
