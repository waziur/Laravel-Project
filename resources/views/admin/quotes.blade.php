@extends('layouts.panel')

@section('panel')
    <section class="panel-section">
        <div class="panel-section-heading">
            <div>
                <p class="panel-kicker mb-1">Database records</p>
                <h2>Quote requests</h2>
            </div>
            <a href="{{ route('admin.quotes.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-2"></i>Add Quote
            </a>
        </div>

        <form action="{{ route('admin.quotes') }}" method="get" class="panel-search">
            <div class="input-group">
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search name, email, service, or message">
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    <option value="pending" @selected($status === 'pending')>Pending</option>
                    <option value="approved" @selected($status === 'approved')>Approved</option>
                </select>
                <button class="btn btn-primary" type="submit"><i class="fa fa-search me-2"></i>Search</button>
            </div>
        </form>

        <div class="panel-table-wrap">
            <table class="table panel-table quote-admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Service</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotes as $quote)
                        <tr>
                            <td>{{ $quote->name }}</td>
                            <td>{{ $quote->email }}</td>
                            <td>{{ $quote->service }}</td>
                            <td class="panel-table-message">{{ $quote->message }}</td>
                            <td>
                                <span class="status-pill {{ $quote->is_approved ? 'status-active' : 'status-inactive' }}">
                                    {{ $quote->is_approved ? 'Approved' : 'Pending' }}
                                </span>
                            </td>
                            <td>{{ $quote->created_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                            <td>
                                <div class="panel-row-actions">
                                    <form action="{{ route('admin.quotes.approval', $quote) }}" method="post">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_approved" value="{{ $quote->is_approved ? 0 : 1 }}">
                                        <button type="submit" class="btn btn-sm {{ $quote->is_approved ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                                            <i class="fa {{ $quote->is_approved ? 'fa-times' : 'fa-check' }} me-1"></i>{{ $quote->is_approved ? 'Unapprove' : 'Approve' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.quotes.edit', $quote) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-edit me-1"></i>Edit
                                    </a>
                                    <form action="{{ route('admin.quotes.destroy', $quote) }}" method="post" onsubmit="return confirm('Delete this quote request?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa fa-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No quote requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $quotes->links() }}
        </div>
    </section>
@endsection
