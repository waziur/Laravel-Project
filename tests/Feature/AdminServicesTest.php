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
            'detail_overview' => 'Plan and automate cloud delivery pipelines with clear release controls.',
            'included_services_text' => "Infrastructure as code setup\nCI/CD pipeline automation",
            'delivery_steps_text' => "Review cloud workflow\nBuild automation plan",
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

    $service = Service::where('title', 'Cloud Automation')->firstOrFail();

    $this->get(route('service.show', $service))
        ->assertOk()
        ->assertSee('Infrastructure as code setup')
        ->assertSee('Build automation plan');
});

test('inactive services stay hidden from the website', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.services.store'), [
            'title' => 'Hidden UX Review',
            'image_url' => 'img/about.jpg',
            'short_description' => 'A service that should not be public yet.',
            'detail_overview' => 'Private review service for internal testing.',
            'included_services_text' => "UX audit\nPrototype notes",
            'delivery_steps_text' => "Review screens\nShare feedback",
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

    $service = Service::where('title', 'Hidden UX Review')->firstOrFail();

    $this->get(route('service.show', $service))
        ->assertNotFound();
});

test('admin can update and delete services', function () {
    $admin = User::factory()->admin()->create();
    $service = Service::create([
        'title' => 'Legacy Service',
        'image_url' => 'img/carousel-1.jpg',
        'short_description' => 'Old service text.',
        'detail_overview' => 'Old service detail text.',
        'included_services' => ['Old planning item'],
        'delivery_steps' => ['Old delivery step'],
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.services.update', $service), [
            'title' => 'Updated Service',
            'image_url' => 'img/carousel-2.jpg',
            'short_description' => 'Updated service description.',
            'detail_overview' => 'Updated detail overview for the service page.',
            'included_services_text' => "Updated discovery\nUpdated implementation",
            'delivery_steps_text' => "Confirm scope\nDeliver update",
            'is_active' => '0',
        ])
        ->assertRedirect(route('admin.services'))
        ->assertSessionHas('status', 'Service updated successfully.');

    $this->assertDatabaseHas('services', [
        'id' => $service->id,
        'title' => 'Updated Service',
        'detail_overview' => 'Updated detail overview for the service page.',
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
