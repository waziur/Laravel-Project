<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        return redirect()->route(
            $request->user()->isAdmin() ? 'admin.dashboard' : 'user.dashboard'
        );
    }

    public function user(Request $request): View
    {
        return view('pages.dashboard.user', [
            'user' => $request->user(),
            'pageTitle' => 'User Dashboard',
            'quickLinks' => $this->quickLinks(),
            'bookingCount' => $request->user()->bookings()->count(),
        ]);
    }

    public function bookings(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $status = in_array($status, array_keys(Booking::statuses()), true) ? $status : '';

        $bookings = $request->user()
            ->bookings()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('service', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->status($status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.dashboard.bookings', [
            'pageTitle' => 'My Bookings',
            'bookings' => $bookings,
            'search' => $search,
            'status' => $status,
            'statuses' => Booking::statuses(),
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function quickLinks(): array
    {
        return [
            [
                'label' => 'Book a Service',
                'description' => 'Send a booking request directly from your account.',
                'icon' => 'fa-calendar-plus',
                'url' => route('booking'),
            ],
            [
                'label' => 'My Bookings',
                'description' => 'Track every booking with details and admin status.',
                'icon' => 'fa-calendar-check',
                'url' => route('user.bookings'),
            ],
            [
                'label' => 'Our Services',
                'description' => 'Explore web, app, security, and cloud service options.',
                'icon' => 'fa-cubes',
                'url' => route('service'),
            ],
            [
                'label' => 'Support Contact',
                'description' => 'Reach the team for account or project support.',
                'icon' => 'fa-headset',
                'url' => route('contact'),
            ],
        ];
    }
}
