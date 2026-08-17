@php
    $bookingServices = isset($activeServices) && $activeServices->isNotEmpty()
        ? $activeServices->pluck('title')->all()
        : ['IT Consultation', 'Web Development', 'App Development', 'Cyber Security'];
    $selectedService = old('service', request('service'));
    $bookingUser = auth()->user();
    $bookingName = old('name', $bookingUser?->name);
    $bookingEmail = old('email', $bookingUser?->email);
    $selectedTime = old('preferred_time');
    $timeSlots = $bookingSlots ?? app(\App\Services\BookingSchedule::class)->slots();
    $minimumBookingDate = $bookingToday ?? app(\App\Services\BookingSchedule::class)->today();
@endphp

@auth
    <form
        action="{{ route('booking.store') }}"
        method="post"
        class="w-100"
        data-toast-pending="Submitting your booking request..."
        data-booking-form
        data-booking-availability-url="{{ route('booking.availability') }}"
    >
        @csrf

        <div class="row g-3">
            <div class="col-xl-12">
                <input type="text" name="name" value="{{ $bookingName }}" class="form-control bg-light border-0 @error('name') is-invalid @enderror" placeholder="Your Name" style="height: 55px;" maxlength="100" autocomplete="name" required>
                @error('name')
                    <div class="invalid-feedback bg-white px-2">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <input type="email" name="email" value="{{ $bookingEmail }}" class="form-control bg-light border-0 @error('email') is-invalid @enderror" placeholder="Your Email" style="height: 55px;" maxlength="150" autocomplete="email" required>
                @error('email')
                    <div class="invalid-feedback bg-white px-2">{{ $message }}</div>
                @enderror
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
                        <option value="{{ $service }}" @selected($selectedService === $service)>{{ $service }}</option>
                    @endforeach
                </select>
                @error('service')
                    <div class="invalid-feedback bg-white px-2">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <input type="date" name="preferred_date" value="{{ old('preferred_date') }}" min="{{ $minimumBookingDate }}" class="form-control bg-light border-0 @error('preferred_date') is-invalid @enderror" style="height: 55px;" data-booking-date required>
                @error('preferred_date')
                    <div class="invalid-feedback bg-white px-2">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <div class="booking-time-picker @error('preferred_time') is-invalid @enderror" data-booking-time-picker>
                    <input type="hidden" name="preferred_time" value="{{ $selectedTime }}" data-booking-time-input aria-required="true">
                    <button type="button" class="booking-time-trigger" data-booking-time-trigger aria-expanded="false">
                        <span class="booking-time-clock" aria-hidden="true">
                            <span class="booking-clock-face">
                                <span class="booking-clock-hand"></span>
                            </span>
                        </span>
                        <span class="booking-time-copy">
                            <small>Preferred Time</small>
                            <strong data-booking-time-label>{{ $selectedTime ?: 'Choose Time' }}</strong>
                        </span>
                        <i class="fa fa-chevron-down"></i>
                    </button>
                    <div class="booking-time-menu" data-booking-time-menu>
                        <div class="booking-time-options" role="listbox" aria-label="Preferred time options">
                            @foreach ($timeSlots as $timeSlot)
                                <button
                                    type="button"
                                    class="booking-time-option {{ $selectedTime === $timeSlot['value'] ? 'active' : '' }}"
                                    data-booking-time-value="{{ $timeSlot['value'] }}"
                                    role="option"
                                    aria-selected="{{ $selectedTime === $timeSlot['value'] ? 'true' : 'false' }}"
                                    aria-disabled="true"
                                    disabled
                                >
                                    {{ $timeSlot['label'] }}
                                </button>
                            @endforeach
                        </div>
                        <p class="booking-availability-status mb-0" data-booking-availability-status aria-live="polite">
                            Select a date to see available times.
                        </p>
                    </div>
                </div>
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

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const forms = document.querySelectorAll('[data-booking-form]');

                if (! forms.length) {
                    return;
                }

                const closePicker = function (picker) {
                    picker.classList.remove('is-open');
                    picker.querySelector('[data-booking-time-trigger]')?.setAttribute('aria-expanded', 'false');
                };

                forms.forEach(function (form) {
                    const picker = form.querySelector('[data-booking-time-picker]');
                    const dateInput = form.querySelector('[data-booking-date]');
                    const trigger = picker.querySelector('[data-booking-time-trigger]');
                    const input = picker.querySelector('[data-booking-time-input]');
                    const label = picker.querySelector('[data-booking-time-label]');
                    const status = picker.querySelector('[data-booking-availability-status]');
                    const options = Array.from(picker.querySelectorAll('[data-booking-time-value]'));
                    const availabilityUrl = form.dataset.bookingAvailabilityUrl;
                    let activeRequest = null;

                    const setStatus = function (message, state) {
                        status.textContent = message;
                        status.dataset.state = state || '';
                    };

                    const clearSelection = function () {
                        input.value = '';
                        label.textContent = 'Choose Time';

                        options.forEach(function (option) {
                            option.classList.remove('active');
                            option.setAttribute('aria-selected', 'false');
                        });
                    };

                    const disableOptions = function () {
                        options.forEach(function (option) {
                            option.disabled = true;
                            option.classList.add('is-unavailable');
                            option.setAttribute('aria-disabled', 'true');
                        });
                    };

                    const loadAvailability = async function () {
                        const date = dateInput.value;

                        if (! date) {
                            activeRequest?.abort();
                            disableOptions();
                            clearSelection();
                            setStatus('Select a date to see available times.', 'idle');

                            return;
                        }

                        activeRequest?.abort();
                        activeRequest = new AbortController();
                        disableOptions();
                        setStatus('Checking the latest availability...', 'loading');

                        try {
                            const url = new URL(availabilityUrl, window.location.origin);
                            url.searchParams.set('date', date);

                            const response = await fetch(url, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                cache: 'no-store',
                                signal: activeRequest.signal,
                            });

                            if (! response.ok) {
                                throw new Error('Availability request failed.');
                            }

                            const payload = await response.json();
                            const slots = new Map(
                                payload.slots.map(function (slot) {
                                    return [slot.value, slot];
                                })
                            );
                            let availableCount = 0;
                            let selectedTimeIsAvailable = input.value === '';

                            options.forEach(function (option) {
                                const slot = slots.get(option.dataset.bookingTimeValue);
                                const isAvailable = Boolean(slot?.available);

                                option.disabled = ! isAvailable;
                                option.classList.toggle('is-unavailable', ! isAvailable);
                                option.setAttribute('aria-disabled', isAvailable ? 'false' : 'true');

                                if (isAvailable) {
                                    availableCount += 1;
                                }

                                if (option.dataset.bookingTimeValue === input.value) {
                                    selectedTimeIsAvailable = isAvailable;
                                }
                            });

                            if (! selectedTimeIsAvailable) {
                                clearSelection();
                            }

                            setStatus(
                                availableCount > 0
                                    ? availableCount + ' time slots are currently available.'
                                    : 'No times are available on this date. Please choose another date.',
                                availableCount > 0 ? 'success' : 'empty'
                            );
                        } catch (error) {
                            if (error.name === 'AbortError') {
                                return;
                            }

                            disableOptions();
                            clearSelection();
                            setStatus(
                                'Availability could not be loaded. Please try again before submitting.',
                                'error'
                            );
                        }
                    };

                    trigger?.addEventListener('click', function () {
                        const isOpen = picker.classList.toggle('is-open');
                        trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                        if (isOpen) {
                            void loadAvailability();
                        }
                    });

                    options.forEach(function (option) {
                        option.addEventListener('click', function () {
                            if (option.disabled) {
                                return;
                            }

                            options.forEach(function (item) {
                                item.classList.remove('active');
                                item.setAttribute('aria-selected', 'false');
                            });

                            option.classList.add('active');
                            option.setAttribute('aria-selected', 'true');
                            input.value = option.dataset.bookingTimeValue;
                            label.textContent = option.dataset.bookingTimeValue;
                            closePicker(picker);
                        });
                    });

                    dateInput.addEventListener('change', function () {
                        clearSelection();
                        void loadAvailability();
                    });

                    if (dateInput.value) {
                        void loadAvailability();
                    }
                });

                document.addEventListener('click', function (event) {
                    document.querySelectorAll('[data-booking-time-picker]').forEach(function (picker) {
                        if (! picker.contains(event.target)) {
                            closePicker(picker);
                        }
                    });
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        document.querySelectorAll('[data-booking-time-picker]').forEach(closePicker);
                    }
                });
            });
        </script>
    @endpush
@endonce
