@extends('layouts.panel')

@section('panel')
    <section class="panel-section">
        <div class="panel-section-heading">
            <div>
                <p class="panel-kicker mb-1">New quote request</p>
                <h2>Add quote</h2>
            </div>
            <a href="{{ route('admin.quotes') }}" class="btn btn-outline-primary">
                <i class="fa fa-arrow-left me-2"></i>Quotes
            </a>
        </div>

        @include('admin.quotes._form', [
            'action' => route('admin.quotes.store'),
            'method' => 'POST',
            'buttonLabel' => 'Create Quote',
        ])
    </section>
@endsection
