@extends('layouts.panel')

@section('panel')
    <section class="panel-hero admin-hero">
        <div class="panel-hero-content">
            <span class="panel-badge"><i class="fa fa-user-shield me-2"></i>Logged in as admin</span>
            <h2>Control center</h2>
            <p>Accounts, website requests, and customer messages are arranged in one clean workspace.</p>
        </div>
        <div class="panel-hero-metrics">
            <div>
                <strong>{{ $stats[0]['value'] }}</strong>
                <span>Users</span>
            </div>
            <div>
                <strong>{{ $stats[3]['value'] }}</strong>
                <span>Quotes</span>
            </div>
            <div>
                <strong>{{ $stats[4]['value'] }}</strong>
                <span>Messages</span>
            </div>
        </div>
    </section>

    <section class="panel-grid dashboard-stats">
        @foreach ($stats as $stat)
            <article class="panel-card stat-card">
                <div class="stat-card-top">
                    <span class="panel-card-icon bg-{{ $stat['tone'] }}"><i class="fa {{ $stat['icon'] }}"></i></span>
                    <span class="stat-chip">Live</span>
                </div>
                <p class="panel-card-label">{{ $stat['label'] }}</p>
                <h3>{{ $stat['value'] }}</h3>
                <small>{{ $stat['note'] }}</small>
            </article>
        @endforeach
    </section>

    <section class="dashboard-workspace">
        <article class="panel-section dashboard-chart-panel">
            <div class="panel-section-heading">
                <div>
                    <p class="panel-kicker mb-1">Overview</p>
                    <h2>Panel snapshot</h2>
                </div>
                <span class="panel-soft-badge">Today</span>
            </div>

            <div class="panel-chart">
                @foreach ($dashboardBars as $bar)
                    <div class="panel-chart-item">
                        <strong>{{ $bar['value'] }}</strong>
                        <div class="panel-chart-track">
                            <span class="panel-chart-fill {{ $bar['tone'] }}" style="--bar-width: {{ $bar['height'] }}%; height: {{ $bar['height'] }}%;"></span>
                        </div>
                        <small>{{ $bar['label'] }}</small>
                    </div>
                @endforeach
            </div>
        </article>

        <aside class="panel-section activity-panel">
            <div class="panel-section-heading">
                <div>
                    <p class="panel-kicker mb-1">Activity</p>
                    <h2>Recent updates</h2>
                </div>
            </div>

            <div class="activity-list">
                @forelse ($recentActivity as $activity)
                    <div class="activity-item">
                        <span class="activity-icon {{ $activity['tone'] }}"><i class="fa {{ $activity['icon'] }}"></i></span>
                        <div>
                            <strong>{{ $activity['title'] }}</strong>
                            <small>{{ $activity['meta'] }}</small>
                        </div>
                        <time>{{ $activity['created_at']?->diffForHumans() ?? 'Now' }}</time>
                    </div>
                @empty
                    <div class="panel-empty-state">
                        <i class="fa fa-inbox"></i>
                        <span>No recent activity yet.</span>
                    </div>
                @endforelse
            </div>
        </aside>
    </section>

    <section class="panel-split-grid">
        <article class="panel-section">
            <div class="panel-section-heading">
                <div>
                    <p class="panel-kicker mb-1">Admin tools</p>
                    <h2>Quick actions</h2>
                </div>
                <a href="{{ route('admin.users') }}" class="btn btn-primary">
                    <i class="fa fa-users me-2"></i>Users
                </a>
            </div>

            <div class="panel-grid action-grid">
                @foreach ($adminTools as $tool)
                    <a href="{{ $tool['url'] }}" class="panel-action">
                        <span><i class="fa {{ $tool['icon'] }}"></i></span>
                        <strong>{{ $tool['label'] }}</strong>
                        <small>{{ $tool['description'] }}</small>
                    </a>
                @endforeach
            </div>
        </article>

        <article class="panel-section">
            <div class="panel-section-heading">
                <div>
                    <p class="panel-kicker mb-1">Website pages</p>
                    <h2>Public map</h2>
                </div>
            </div>

            <div class="site-link-grid">
                @foreach ($siteLinks as $link)
                    <a href="{{ $link['url'] }}">
                        <i class="fa {{ $link['icon'] }}"></i>
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </article>
    </section>

    <section class="panel-section">
        <div class="panel-section-heading">
            <div>
                <p class="panel-kicker mb-1">Latest accounts</p>
                <h2>Recent users</h2>
            </div>
        </div>

        <div class="panel-table-wrap">
            <table class="table panel-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latestUsers as $latestUser)
                        <tr>
                            <td>{{ $latestUser->name }}</td>
                            <td>{{ $latestUser->email }}</td>
                            <td><span class="role-pill role-{{ $latestUser->roleSlug() }}">{{ $latestUser->roleName() }}</span></td>
                            <td>{{ $latestUser->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
