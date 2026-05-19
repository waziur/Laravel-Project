@php
    $approvedValue = old('is_approved', $quote->exists ? (string) (int) $quote->is_approved : '0');
@endphp

<form action="{{ $action }}" method="post" class="service-admin-form">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="service-form-layout">
        <div class="service-form-fields">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $quote->name) }}" class="form-control @error('name') is-invalid @enderror" required minlength="2" maxlength="100" autocomplete="name">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $quote->email) }}" class="form-control @error('email') is-invalid @enderror" required maxlength="150" autocomplete="email">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="service" class="form-label">Service</label>
                <input type="text" id="service" name="service" value="{{ old('service', $quote->service) }}" class="form-control @error('service') is-invalid @enderror" list="quote-service-options" required maxlength="100">
                <datalist id="quote-service-options">
                    @foreach ($serviceOptions as $service)
                        <option value="{{ $service }}"></option>
                    @endforeach
                </datalist>
                @error('service')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea id="message" name="message" rows="6" class="form-control @error('message') is-invalid @enderror" required minlength="1" maxlength="2000">{{ old('message', $quote->message) }}</textarea>
                @error('message')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <input type="hidden" name="is_approved" value="0">
            <label class="service-status-toggle">
                <input type="checkbox" name="is_approved" value="1" @checked($approvedValue === '1')>
                <span>
                    <strong>Approved quote</strong>
                    <small>Show this quote request on the public homepage.</small>
                </span>
            </label>
        </div>

        <aside class="service-form-preview">
            <span class="panel-kicker mb-2">Public preview</span>
            <div class="approved-quote-card bg-white p-4">
                <span class="approved-quote-service">{{ old('service', $quote->service ?: 'Service') }}</span>
                <p>{{ old('message', $quote->message ?: 'The quote request message will appear here after approval.') }}</p>
                <div class="approved-quote-author">
                    <span>{{ strtoupper(substr(old('name', $quote->name ?: 'Client'), 0, 1)) }}</span>
                    <div>
                        <strong>{{ old('name', $quote->name ?: 'Client Name') }}</strong>
                        <small>{{ $approvedValue === '1' ? 'Approved for frontend' : 'Pending approval' }}</small>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <div class="service-form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-2"></i>{{ $buttonLabel }}
        </button>
        <a href="{{ route('admin.quotes') }}" class="btn btn-outline-primary">Cancel</a>
    </div>
</form>
