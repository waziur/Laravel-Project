@extends('layouts.panel')

@section('panel')
    <section class="panel-section">
        <div class="panel-section-heading">
            <div>
                <p class="panel-kicker mb-1">Update quote request</p>
                <h2>Edit quote</h2>
            </div>
            <a href="{{ route('admin.quotes') }}" class="btn btn-outline-primary">
                <i class="fa fa-arrow-left me-2"></i>Quotes
            </a>
        </div>

        @include('admin.quotes._form', [
            'action' => route('admin.quotes.update', $quote),
            'method' => 'PUT',
            'buttonLabel' => 'Update Quote',
        ])
    </section>
@endsection
