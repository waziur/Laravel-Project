@extends('layouts.panel')

@section('panel')
    <section class="panel-section">
        <div class="panel-section-heading">
            <div>
                <p class="panel-kicker mb-1">Website services</p>
                <h2>Service manager</h2>
            </div>
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-2"></i>Add Service
            </a>
        </div>

        <form action="{{ route('admin.services') }}" method="get" class="panel-search">
            <div class="input-group">
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search title or description">
                <button class="btn btn-primary" type="submit"><i class="fa fa-search me-2"></i>Search</button>
            </div>
        </form>

        <div class="panel-table-wrap">
            <table class="table panel-table service-admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td>
                                <img src="{{ $service->imageSrc() }}" alt="{{ $service->title }}" class="service-admin-thumb">
                            </td>
                            <td><strong>{{ $service->title }}</strong></td>
                            <td class="panel-table-message">{{ $service->short_description }}</td>
                            <td>
                                <span class="status-pill {{ $service->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $service->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $service->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                            <td>
                                <div class="panel-row-actions">
                                    <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-edit me-1"></i>Edit
                                    </a>
                                    <form action="{{ route('admin.services.destroy', $service) }}" method="post" onsubmit="return confirm('Delete this service?')">
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
                            <td colspan="6" class="text-center py-4">No services found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $services->links() }}
        </div>
    </section>
@endsection
