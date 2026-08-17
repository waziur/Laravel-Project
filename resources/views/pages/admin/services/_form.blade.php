@php
    $imageValue = old('image_url', $service->image_url ?: 'img/feature.jpg');
    $imagePreview = \Illuminate\Support\Str::startsWith($imageValue, ['http://', 'https://', '//'])
        ? $imageValue
        : asset($imageValue);
    $activeValue = old('is_active', $service->exists ? (string) (int) $service->is_active : '1');
    $includedServicesText = old('included_services_text', implode(PHP_EOL, $service->includedServiceList()));
    $deliveryStepsText = old('delivery_steps_text', implode(PHP_EOL, $service->deliveryStepList()));
@endphp

<form action="{{ $action }}" method="post" class="service-admin-form">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="service-form-layout">
        <div class="service-form-fields">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" id="title" name="title" value="{{ old('title', $service->title) }}" class="form-control @error('title') is-invalid @enderror" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="image_url" class="form-label">Image URL</label>
                <input type="text" id="image_url" name="image_url" value="{{ $imageValue }}" class="form-control @error('image_url') is-invalid @enderror" placeholder="https://example.com/service.jpg" required>
                <small class="text-muted">External image URL or a public path like img/about.jpg.</small>
                @error('image_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="short_description" class="form-label">Short Description</label>
                <textarea id="short_description" name="short_description" rows="5" class="form-control @error('short_description') is-invalid @enderror" required>{{ old('short_description', $service->short_description) }}</textarea>
                @error('short_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="detail_overview" class="form-label">Detail Overview</label>
                <textarea id="detail_overview" name="detail_overview" rows="6" class="form-control @error('detail_overview') is-invalid @enderror" required>{{ old('detail_overview', $service->detail_overview) }}</textarea>
                <small class="text-muted">This appears at the top of the single service detail page.</small>
                @error('detail_overview')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="included_services_text" class="form-label">Specific Services Provided</label>
                <textarea id="included_services_text" name="included_services_text" rows="6" class="form-control @error('included_services_text') is-invalid @enderror" required>{{ $includedServicesText }}</textarea>
                <small class="text-muted">Write one specific service per line.</small>
                @error('included_services_text')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="delivery_steps_text" class="form-label">Delivery Process Steps</label>
                <textarea id="delivery_steps_text" name="delivery_steps_text" rows="6" class="form-control @error('delivery_steps_text') is-invalid @enderror" required>{{ $deliveryStepsText }}</textarea>
                <small class="text-muted">Write one process step per line.</small>
                @error('delivery_steps_text')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <input type="hidden" name="is_active" value="0">
            <label class="service-status-toggle">
                <input type="checkbox" name="is_active" value="1" @checked($activeValue === '1')>
                <span>
                    <strong>Active service</strong>
                    <small>Show this service on the public website.</small>
                </span>
            </label>
        </div>

        <aside class="service-form-preview">
            <span class="panel-kicker mb-2">Preview</span>
            <div class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
                <div class="service-photo">
                    <img src="{{ $imagePreview }}" alt="Service preview">
                </div>
                <h4 class="mb-3">{{ old('title', $service->title ?: 'Service title') }}</h4>
                <p class="m-0">{{ old('short_description', $service->short_description ?: 'Short service description will appear here.') }}</p>
            </div>
        </aside>
    </div>

    <div class="service-form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-2"></i>{{ $buttonLabel }}
        </button>
        <a href="{{ route('admin.services') }}" class="btn btn-outline-primary">Cancel</a>
    </div>
</form>
