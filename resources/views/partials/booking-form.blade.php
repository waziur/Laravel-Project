@php
    $bookingServices = isset($activeServices) && $activeServices->isNotEmpty()
        ? $activeServices->pluck('title')->all()
        : ['IT Consultation', 'Web Development', 'App Development', 'Cyber Security'];
@endphp

@auth
    @php($bookingUser = auth()->user())

    <form action="{{ route('booking.store') }}" method="post" class="w-100" data-toast-pending="Submitting your booking request...">
        @csrf

        <div class="row g-3">
            <div class="col-xl-12">
                <input type="text" value="{{ $bookingUser->name }}" class="form-control bg-light border-0" placeholder="Your Name" style="height: 55px;" readonly>
            </div>
            <div class="col-12">
                <input type="email" value="{{ $bookingUser->email }}" class="form-control bg-light border-0" placeholder="Your Email" style="height: 55px;" readonly>
            </div>
            <div class="col-12">
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control bg-light border-0 @error('phone') is-invalid @enderror" placeholder="Phone Number" style="height: 55px;" maxlength="30" autocomplete="tel">
                @error('phone')
                    <div class="invalid-feedback bg-white px-2">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <select name="service" class="form-select bg-light border-0 @error('service') is-invalid @enderror" style="height: 55px;" required>
                    <option value="">Select A Service</option>
                    @foreach ($bookingServices as $service)
                        <option value="{{ $service }}" @selected(old('service') === $service)>{{ $service }}</option>
                    @endforeach
                </select>
                @error('service')
                    <div class="invalid-feedback bg-white px-2">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <input type="date" name="preferred_date" value="{{ old('preferred_date') }}" min="{{ now()->toDateString() }}" class="form-control bg-light border-0 @error('preferred_date') is-invalid @enderror" style="height: 55px;" required>
                @error('preferred_date')
                    <div class="invalid-feedback bg-white px-2">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <input type="text" name="preferred_time" value="{{ old('preferred_time') }}" class="form-control bg-light border-0 @error('preferred_time') is-invalid @enderror" placeholder="Preferred Time" style="height: 55px;" maxlength="50">
                @error('preferred_time')
                    <div class="invalid-feedback bg-white px-2">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <textarea name="message" class="form-control bg-light border-0 @error('message') is-invalid @enderror" rows="3" placeholder="Booking Details" required minlength="10" maxlength="2000">{{ old('message') }}</textarea>
                @error('message')
                    <div class="invalid-feedback bg-white px-2">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <button class="btn btn-dark w-100 py-3" type="submit">Submit Booking</button>
            </div>
        </div>
    </form>
@else
    <div class="w-100 text-white">
        <span class="auth-side-icon mb-4"><i class="fa fa-lock"></i></span>
        <h3 class="text-white mb-3">Login required for booking</h3>
        <p class="text-white-50 mb-4">Please login or create an account first. After that you can submit a booking from your own dashboard account.</p>
        <div class="d-grid gap-3">
            <a href="{{ route('login', ['redirect' => route('booking')]) }}" class="btn btn-dark py-3">
                <i class="fa fa-sign-in-alt me-2"></i>Login
            </a>
            <a href="{{ route('register', ['redirect' => route('booking')]) }}" class="btn btn-light py-3">
                <i class="fa fa-user-plus me-2"></i>Create Account
            </a>
        </div>
    </div>
@endauth
