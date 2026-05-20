@extends('layouts.panel')

@section('panel')
    <section class="panel-section">
        <div class="panel-section-heading">
            <div>
                <p class="panel-kicker mb-1">Customer requests</p>
                <h2>Bookings</h2>
            </div>
            <a href="{{ route('booking') }}" class="btn btn-primary">
                <i class="fa fa-calendar-plus me-2"></i>New Booking
            </a>
        </div>

        <form action="{{ route('admin.bookings') }}" method="get" class="panel-search">
            <div class="input-group">
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search user, email, phone, service, or message">
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary" type="submit"><i class="fa fa-search me-2"></i>Search</button>
            </div>
        </form>

        <div class="panel-table-wrap">
            <table class="table panel-table booking-admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Booked By</th>
                        <th>Contact</th>
                        <th>Service</th>
                        <th>Schedule</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td>
                                <strong>{{ $booking->name }}</strong>
                                <small class="d-block text-muted">Account #{{ $booking->user_id }}{{ $booking->user ? ' - '.$booking->user->roleName() : '' }}</small>
                            </td>
                            <td>
                                <span class="d-block">{{ $booking->email }}</span>
                                <small class="text-muted">{{ $booking->phone ?: 'No phone added' }}</small>
                            </td>
                            <td>{{ $booking->service }}</td>
                            <td>{{ $booking->scheduleLabel() }}</td>
                            <td class="panel-table-message">{{ $booking->message }}</td>
                            <td>
                                <span class="status-pill {{ $booking->statusClass() }}">
                                    {{ $booking->statusLabel() }}
                                </span>
                            </td>
                            <td>{{ $booking->created_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                            <td>
                                <div class="panel-row-actions">
                                    @foreach ($statuses as $value => $label)
                                        @php
                                            $buttonClass = match ($value) {
                                                'accepted' => 'btn-outline-success',
                                                'rejected' => 'btn-outline-danger',
                                                default => 'btn-outline-secondary',
                                            };
                                            $buttonIcon = match ($value) {
                                                'accepted' => 'fa-check',
                                                'rejected' => 'fa-times',
                                                default => 'fa-clock',
                                            };
                                            $buttonLabel = match ($value) {
                                                'accepted' => 'Accept',
                                                'rejected' => 'Reject',
                                                default => 'Pending',
                                            };
                                        @endphp
                                        <form action="{{ route('admin.bookings.status', $booking) }}" method="post">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $value }}">
                                            <button type="submit" class="btn btn-sm {{ $buttonClass }}" @disabled($booking->status === $value)>
                                                <i class="fa {{ $buttonIcon }} me-1"></i>{{ $buttonLabel }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">No bookings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $bookings->links() }}
        </div>
    </section>
@endsection
