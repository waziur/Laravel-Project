@php
    $quoteServices = isset($activeServices) && $activeServices->isNotEmpty()
        ? $activeServices->pluck('title')->all()
        : ['IT Consultation', 'Web Development', 'App Development', 'Cyber Security'];
@endphp

<form action="{{ route('quote.store') }}" method="post" class="w-100" data-toast-pending="Submitting your quote request...">
    @csrf

    <div class="row g-3">
        <div class="col-xl-12">
            <input type="text" name="name" value="{{ old('name') }}" class="form-control bg-light border-0 @error('name') is-invalid @enderror" placeholder="Your Name" style="height: 55px;" required minlength="2" maxlength="100" autocomplete="name">
            @error('name')
                <div class="invalid-feedback bg-white px-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <input type="email" name="email" value="{{ old('email') }}" class="form-control bg-light border-0 @error('email') is-invalid @enderror" placeholder="Your Email" style="height: 55px;" required maxlength="150" autocomplete="email">
            @error('email')
                <div class="invalid-feedback bg-white px-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <select name="service" class="form-select bg-light border-0 @error('service') is-invalid @enderror" style="height: 55px;" required>
                <option value="">Select A Service</option>
                @foreach ($quoteServices as $service)
                    <option value="{{ $service }}" @selected(old('service') === $service)>{{ $service }}</option>
                @endforeach
            </select>
            @error('service')
                <div class="invalid-feedback bg-white px-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <textarea name="message" class="form-control bg-light border-0 @error('message') is-invalid @enderror" rows="3" placeholder="Message" required minlength="10" maxlength="2000">{{ old('message') }}</textarea>
            @error('message')
                <div class="invalid-feedback bg-white px-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <button class="btn btn-dark w-100 py-3" type="submit">Request A Quote</button>
        </div>
    </div>
</form>
