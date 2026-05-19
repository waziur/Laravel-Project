@extends('layouts.panel')

@section('panel')
    <section class="panel-section">
        <div class="panel-section-heading">
            <div>
                <p class="panel-kicker mb-1">New service</p>
                <h2>Add service</h2>
            </div>
            <a href="{{ route('admin.services') }}" class="btn btn-outline-primary">
                <i class="fa fa-arrow-left me-2"></i>Services
            </a>
        </div>

        @include('admin.services._form', [
            'action' => route('admin.services.store'),
            'method' => 'POST',
            'buttonLabel' => 'Create Service',
        ])
    </section>
@endsection
