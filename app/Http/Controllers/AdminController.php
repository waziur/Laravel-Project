<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $counts = $this->dashboardCounts();
        $latestUsers = User::latest()->take(6)->get();
        $latestBookings = Booking::with('user')->latest()->take(4)->get();
        $latestMessages = ContactMessage::latest()->take(4)->get();

        return view('pages.admin.dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'stats' => $this->dashboardStats($counts),
            'latestUsers' => $latestUsers,
            'dashboardBars' => $this->dashboardBars($counts),
            'recentActivity' => $this->recentActivity($latestUsers, $latestBookings, $latestMessages),
            'siteLinks' => $this->siteLinks(),
            'adminTools' => $this->adminTools(),
        ]);
    }

    public function services(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $services = Service::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.services.index', [
            'pageTitle' => 'Manage Services',
            'services' => $services,
            'search' => $search,
        ]);
    }

    public function createService(): View
    {
        return view('pages.admin.services.create', [
            'pageTitle' => 'Add Service',
            'service' => new Service(['is_active' => true]),
        ]);
    }

    public function storeService(Request $request): RedirectResponse
    {
        Service::create($this->validatedServiceData($request));

        return redirect()
            ->route('admin.services')
            ->with('status', 'Service created successfully.');
    }

    public function editService(Service $service): View
    {
        return view('pages.admin.services.edit', [
            'pageTitle' => 'Edit Service',
            'service' => $service,
        ]);
    }

    public function updateService(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validatedServiceData($request));

        return redirect()
            ->route('admin.services')
            ->with('status', 'Service updated successfully.');
    }

    public function destroyService(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()
            ->route('admin.services')
            ->with('status', 'Service deleted successfully.');
    }

    public function bookings(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $status = in_array($status, array_keys(Booking::statuses()), true) ? $status : '';

        $bookings = Booking::with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('service', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn ($query) => $query->status($status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.bookings', [
            'pageTitle' => 'Bookings',
            'bookings' => $bookings,
            'search' => $search,
            'status' => $status,
            'statuses' => Booking::statuses(),
        ]);
    }

    public function updateBookingStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Booking::statuses()))],
        ]);

        $booking->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.bookings')
            ->with('status', 'Booking status updated to '.strtolower($booking->statusLabel()).'.');
    }

    public function contacts(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $messages = ContactMessage::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.contacts', [
            'pageTitle' => 'Contact Messages',
            'messages' => $messages,
            'search' => $search,
        ]);
    }

    public function users(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $normalizedSearch = strtolower($search);

                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");

                    if (str_contains($normalizedSearch, User::ROLE_ADMIN)) {
                        $query->orWhere('is_admin', true);
                    }

                    if (str_contains($normalizedSearch, User::ROLE_USER)) {
                        $query->orWhere('is_admin', false);
                    }
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.users', [
            'pageTitle' => 'Manage Users',
            'users' => $users,
            'search' => $search,
        ]);
    }

    /**
     * @return array{totalUsers: int, adminUsers: int, regularUsers: int, bookingRequests: int, contactMessages: int, activeServices: int}
     */
    private function dashboardCounts(): array
    {
        return [
            'totalUsers' => User::count(),
            'adminUsers' => User::where('is_admin', true)->count(),
            'regularUsers' => User::where('is_admin', false)->count(),
            'bookingRequests' => Booking::count(),
            'contactMessages' => ContactMessage::count(),
            'activeServices' => Service::active()->count(),
        ];
    }

    /**
     * @param  array{totalUsers: int, adminUsers: int, regularUsers: int, bookingRequests: int, contactMessages: int, activeServices: int}  $counts
     * @return array<int, array{label: string, value: int, icon: string, tone: string, note: string}>
     */
    private function dashboardStats(array $counts): array
    {
        return [
            [
                'label' => 'Total Users',
                'value' => $counts['totalUsers'],
                'icon' => 'fa-users',
                'tone' => 'primary',
                'note' => 'Registered accounts',
            ],
            [
                'label' => 'Admins',
                'value' => $counts['adminUsers'],
                'icon' => 'fa-user-shield',
                'tone' => 'success',
                'note' => 'Privileged users',
            ],
            [
                'label' => 'Customers',
                'value' => $counts['regularUsers'],
                'icon' => 'fa-user-check',
                'tone' => 'info',
                'note' => 'Regular users',
            ],
            [
                'label' => 'Bookings',
                'value' => $counts['bookingRequests'],
                'icon' => 'fa-calendar-check',
                'tone' => 'warning',
                'note' => 'Customer booking requests',
            ],
            [
                'label' => 'Messages',
                'value' => $counts['contactMessages'],
                'icon' => 'fa-envelope-open-text',
                'tone' => 'secondary',
                'note' => 'Contact inbox',
            ],
            [
                'label' => 'Active Services',
                'value' => $counts['activeServices'],
                'icon' => 'fa-cubes',
                'tone' => 'primary',
                'note' => 'Visible on site',
            ],
        ];
    }

    /**
     * @param  array{totalUsers: int, adminUsers: int, regularUsers: int, bookingRequests: int, contactMessages: int, activeServices: int}  $counts
     * @return array<int, array{label: string, value: int, tone: string, height: int}>
     */
    private function dashboardBars(array $counts): array
    {
        $bars = [
            ['label' => 'Users', 'value' => $counts['totalUsers'], 'tone' => 'tone-cyan'],
            ['label' => 'Admins', 'value' => $counts['adminUsers'], 'tone' => 'tone-green'],
            ['label' => 'Bookings', 'value' => $counts['bookingRequests'], 'tone' => 'tone-amber'],
            ['label' => 'Messages', 'value' => $counts['contactMessages'], 'tone' => 'tone-rose'],
        ];

        $highestBarValue = max(array_column($bars, 'value')) ?: 1;

        foreach ($bars as $index => $bar) {
            $bars[$index]['height'] = max(18, (int) round(($bar['value'] / $highestBarValue) * 100));
        }

        return $bars;
    }

    /**
     * @param  iterable<User>  $latestUsers
     * @param  iterable<Booking>  $latestBookings
     * @param  iterable<ContactMessage>  $latestMessages
     * @return Collection<int, array{title: string, meta: string, icon: string, tone: string, created_at: Carbon|null}>
     */
    private function recentActivity(iterable $latestUsers, iterable $latestBookings, iterable $latestMessages): Collection
    {
        $activities = [];

        foreach ($latestUsers as $user) {
            $activities[] = [
                'title' => $user->name,
                'meta' => 'New '.strtolower($user->roleName()).' account',
                'icon' => 'fa-user-plus',
                'tone' => 'tone-cyan',
                'created_at' => $user->created_at,
            ];
        }

        foreach ($latestBookings as $booking) {
            $activities[] = [
                'title' => $booking->name,
                'meta' => 'Booking for '.$booking->service.' is '.strtolower($booking->statusLabel()),
                'icon' => 'fa-calendar-check',
                'tone' => 'tone-amber',
                'created_at' => $booking->created_at,
            ];
        }

        foreach ($latestMessages as $message) {
            $activities[] = [
                'title' => $message->name,
                'meta' => $message->subject,
                'icon' => 'fa-envelope-open-text',
                'tone' => 'tone-green',
                'created_at' => $message->created_at,
            ];
        }

        return collect($activities)
            ->sortByDesc(fn (array $activity): int => $activity['created_at']?->timestamp ?? 0)
            ->take(6)
            ->values();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function siteLinks(): array
    {
        return [
            ['label' => 'Home', 'url' => route('home'), 'icon' => 'fa-home'],
            ['label' => 'About', 'url' => route('about'), 'icon' => 'fa-building'],
            ['label' => 'Services', 'url' => route('service'), 'icon' => 'fa-cubes'],
            ['label' => 'Features', 'url' => route('feature'), 'icon' => 'fa-star'],
            ['label' => 'Team', 'url' => route('team'), 'icon' => 'fa-users-cog'],
            ['label' => 'Testimonials', 'url' => route('testimonial'), 'icon' => 'fa-comment-dots'],
            ['label' => 'Booking', 'url' => route('booking'), 'icon' => 'fa-calendar-check'],
            ['label' => 'Contact', 'url' => route('contact'), 'icon' => 'fa-headset'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function adminTools(): array
    {
        return [
            [
                'label' => 'User Directory',
                'description' => 'Search users and check account roles.',
                'url' => route('admin.users'),
                'icon' => 'fa-address-book',
            ],
            [
                'label' => 'Service Manager',
                'description' => 'Add, edit, hide, or delete services from the website.',
                'url' => route('admin.services'),
                'icon' => 'fa-cubes',
            ],
            [
                'label' => 'Frontend Preview',
                'description' => 'Open the live public website from the admin hub.',
                'url' => route('home'),
                'icon' => 'fa-globe',
            ],
            [
                'label' => 'Bookings',
                'description' => 'Review customer bookings and manage approval status.',
                'url' => route('admin.bookings'),
                'icon' => 'fa-calendar-check',
            ],
            [
                'label' => 'Contact Messages',
                'description' => 'Read customer contact form submissions.',
                'url' => route('admin.contacts'),
                'icon' => 'fa-envelope-open-text',
            ],
        ];
    }

    /**
     * @return array{title: string, image_url: string, short_description: string, is_active: bool}
     */
    private function validatedServiceData(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'image_url' => ['required', 'string', 'max:500'],
            'short_description' => ['required', 'string', 'max:500'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
