@extends('layouts.panel')

@section('panel')
    <section class="panel-section">
        <div class="panel-section-heading">
            <div>
                <p class="panel-kicker mb-1">Your history</p>
                <h2>My Bookings</h2>
            </div>
            <a href="{{ route('booking') }}" class="btn btn-primary">
                <i class="fa fa-calendar-plus me-2"></i>Book Again
            </a>
        </div>

        <form action="{{ route('user.bookings') }}" method="get" class="panel-search">
            <div class="input-group">
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search name, email, phone, service, or details">
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
                        <th>Service</th>
                        <th>Schedule</th>
                        <th>Contact</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td>{{ $booking->service }}</td>
                            <td>{{ $booking->scheduleLabel() }}</td>
                            <td>
                                <strong class="d-block">{{ $booking->name }}</strong>
                                <span class="d-block">{{ $booking->email }}</span>
                                <small class="text-muted">{{ $booking->phone ?: 'No phone added' }}</small>
                            </td>
                            <td class="panel-table-message">{{ $booking->message }}</td>
                            <td>
                                <span class="status-pill {{ $booking->statusClass() }}">
                                    {{ $booking->statusLabel() }}
                                </span>
                            </td>
                            <td>{{ $booking->created_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                            <td>{{ $booking->updated_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">You have not submitted any bookings yet.</td>
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
