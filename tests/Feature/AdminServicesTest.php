<?php

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can create an active service that appears on the website', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.services.store'), [
            'title' => 'Cloud Automation',
            'image_url' => 'img/feature.jpg',
            'short_description' => 'Automated cloud workflows for faster delivery.',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.services'))
        ->assertSessionHas('status', 'Service created successfully.');

    $this->assertDatabaseHas('services', [
        'title' => 'Cloud Automation',
        'is_active' => true,
    ]);

    $this->get(route('service'))
        ->assertOk()
        ->assertSee('Cloud Automation');
});

test('inactive services stay hidden from the website', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.services.store'), [
            'title' => 'Hidden UX Review',
            'image_url' => 'img/about.jpg',
            'short_description' => 'A service that should not be public yet.',
            'is_active' => '0',
        ])
        ->assertRedirect(route('admin.services'));

    $this->assertDatabaseHas('services', [
        'title' => 'Hidden UX Review',
        'is_active' => false,
    ]);

    $this->get(route('service'))
        ->assertOk()
        ->assertDontSee('Hidden UX Review');
});

test('admin can update and delete services', function () {
    $admin = User::factory()->admin()->create();
    $service = Service::create([
        'title' => 'Legacy Service',
        'image_url' => 'img/carousel-1.jpg',
        'short_description' => 'Old service text.',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.services.update', $service), [
            'title' => 'Updated Service',
            'image_url' => 'img/carousel-2.jpg',
            'short_description' => 'Updated service description.',
            'is_active' => '0',
        ])
        ->assertRedirect(route('admin.services'))
        ->assertSessionHas('status', 'Service updated successfully.');

    $this->assertDatabaseHas('services', [
        'id' => $service->id,
        'title' => 'Updated Service',
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.services.destroy', $service))
        ->assertRedirect(route('admin.services'))
        ->assertSessionHas('status', 'Service deleted successfully.');

    $this->assertDatabaseMissing('services', [
        'id' => $service->id,
    ]);
});
