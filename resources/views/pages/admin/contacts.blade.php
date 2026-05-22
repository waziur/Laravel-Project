@extends('master.panel')

@section('panel')
    <section class="panel-section">
        <div class="panel-section-heading">
            <div>
                <p class="panel-kicker mb-1">Database records</p>
                <h2>Contact messages</h2>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">
                <i class="fa fa-arrow-left me-2"></i>Dashboard
            </a>
        </div>

        <form action="{{ route('admin.contacts') }}" method="get" class="panel-search">
            <div class="input-group">
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search name, email, or subject">
                <button class="btn btn-primary" type="submit"><i class="fa fa-search me-2"></i>Search</button>
            </div>
        </form>

        <div class="panel-table-wrap">
            <table class="table panel-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $message)
                        <tr>
                            <td>{{ $message->name }}</td>
                            <td>{{ $message->email }}</td>
                            <td>{{ $message->subject }}</td>
                            <td class="panel-table-message">{{ $message->message }}</td>
                            <td>{{ $message->created_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No contact messages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $messages->links() }}
        </div>
    </section>
@endsection
