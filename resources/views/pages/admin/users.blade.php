@extends('master.panel')

@section('panel')
    <section class="panel-section">
        <div class="panel-section-heading">
            <div>
                <p class="panel-kicker mb-1">Accounts</p>
                <h2>User directory</h2>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">
                <i class="fa fa-arrow-left me-2"></i>Dashboard
            </a>
        </div>

        <form action="{{ route('admin.users') }}" method="get" class="panel-search">
            <div class="input-group">
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search name, email, or role">
                <button class="btn btn-primary" type="submit"><i class="fa fa-search me-2"></i>Search</button>
            </div>
        </form>

        <div class="panel-table-wrap">
            <table class="table panel-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Admin Boolean</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="role-pill role-{{ $user->roleSlug() }}">{{ $user->roleName() }}</span></td>
                            <td>{{ $user->is_admin ? '1 = Admin' : '0 = User' }}</td>
                            <td>{{ $user->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No matching users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </section>
@endsection
