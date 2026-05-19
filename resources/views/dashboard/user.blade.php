@extends('layouts.panel')

@section('panel')
    <section class="panel-hero user-hero">
        <div>
            <span class="panel-badge"><i class="fa fa-user me-2"></i>Logged in as user</span>
            <h2>Welcome, {{ $user->name }}</h2>
            <p>Your user panel keeps service requests, contact options, and website links close together.</p>
        </div>
        <div class="panel-hero-icon">
            <i class="fa fa-user-check"></i>
        </div>
    </section>

    <section class="panel-grid three-columns">
        <article class="panel-card">
            <span class="panel-card-icon bg-primary"><i class="fa fa-envelope"></i></span>
            <p class="panel-card-label">Email</p>
            <h3>{{ $user->email }}</h3>
        </article>
        <article class="panel-card">
            <span class="panel-card-icon bg-success"><i class="fa fa-id-badge"></i></span>
            <p class="panel-card-label">Role</p>
            <h3>{{ $user->roleName() }}</h3>
        </article>
        <article class="panel-card">
            <span class="panel-card-icon bg-info"><i class="fa fa-calendar-alt"></i></span>
            <p class="panel-card-label">Joined</p>
            <h3>{{ $user->created_at?->format('M d, Y') ?? 'Today' }}</h3>
        </article>
    </section>

    <section class="panel-section">
        <div class="panel-section-heading">
            <div>
                <p class="panel-kicker mb-1">Quick access</p>
                <h2>Common actions</h2>
            </div>
        </div>

        <div class="panel-grid three-columns">
            @foreach ($quickLinks as $link)
                <a href="{{ $link['url'] }}" class="panel-action">
                    <span><i class="fa {{ $link['icon'] }}"></i></span>
                    <strong>{{ $link['label'] }}</strong>
                    <small>{{ $link['description'] }}</small>
                </a>
            @endforeach
        </div>
    </section>
@endsection
