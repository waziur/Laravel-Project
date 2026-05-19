<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $totalUsers = User::count();
        $adminUsers = User::where('is_admin', true)->count();
        $regularUsers = User::where('is_admin', false)->count();
        $quoteRequests = QuoteRequest::count();
        $contactMessages = ContactMessage::count();
        $activeServices = Service::active()->count();
        $latestUsers = User::latest()->take(6)->get();
        $latestQuotes = QuoteRequest::latest()->take(4)->get();
        $latestMessages = ContactMessage::latest()->take(4)->get();

        $dashboardBars = [
            ['label' => 'Users', 'value' => $totalUsers, 'tone' => 'tone-cyan'],
            ['label' => 'Admins', 'value' => $adminUsers, 'tone' => 'tone-green'],
            ['label' => 'Quotes', 'value' => $quoteRequests, 'tone' => 'tone-amber'],
            ['label' => 'Messages', 'value' => $contactMessages, 'tone' => 'tone-rose'],
        ];

        $highestBarValue = max(array_column($dashboardBars, 'value')) ?: 1;

        $dashboardBars = array_map(function (array $bar) use ($highestBarValue) {
            $bar['height'] = max(18, (int) round(($bar['value'] / $highestBarValue) * 100));

            return $bar;
        }, $dashboardBars);

        $recentActivity = collect()
            ->concat($latestUsers->map(fn (User $user) => [
                'title' => $user->name,
                'meta' => 'New ' . strtolower($user->roleName()) . ' account',
                'icon' => 'fa-user-plus',
                'tone' => 'tone-cyan',
                'created_at' => $user->created_at,
            ]))
            ->concat($latestQuotes->map(fn (QuoteRequest $quote) => [
                'title' => $quote->name,
                'meta' => 'Quote request for ' . $quote->service,
                'icon' => 'fa-file-signature',
                'tone' => 'tone-amber',
                'created_at' => $quote->created_at,
            ]))
            ->concat($latestMessages->map(fn (ContactMessage $message) => [
                'title' => $message->name,
                'meta' => $message->subject,
                'icon' => 'fa-envelope-open-text',
                'tone' => 'tone-green',
                'created_at' => $message->created_at,
            ]))
            ->sortByDesc(fn (array $activity) => $activity['created_at']?->timestamp ?? 0)
            ->take(6)
            ->values();

        return view('admin.dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'stats' => [
                [
                    'label' => 'Total Users',
                    'value' => $totalUsers,
                    'icon' => 'fa-users',
                    'tone' => 'primary',
                    'note' => 'Registered accounts',
                ],
                [
                    'label' => 'Admins',
                    'value' => $adminUsers,
                    'icon' => 'fa-user-shield',
                    'tone' => 'success',
                    'note' => 'Privileged users',
                ],
                [
                    'label' => 'Customers',
                    'value' => $regularUsers,
                    'icon' => 'fa-user-check',
                    'tone' => 'info',
                    'note' => 'Regular users',
                ],
                [
                    'label' => 'Quote Requests',
                    'value' => $quoteRequests,
                    'icon' => 'fa-file-signature',
                    'tone' => 'warning',
                    'note' => 'Service inquiries',
                ],
                [
                    'label' => 'Messages',
                    'value' => $contactMessages,
                    'icon' => 'fa-envelope-open-text',
                    'tone' => 'secondary',
                    'note' => 'Contact inbox',
                ],
                [
                    'label' => 'Active Services',
                    'value' => $activeServices,
                    'icon' => 'fa-cubes',
                    'tone' => 'primary',
                    'note' => 'Visible on site',
                ],
            ],
            'latestUsers' => $latestUsers,
            'dashboardBars' => $dashboardBars,
            'recentActivity' => $recentActivity,
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

        return view('admin.services.index', [
            'pageTitle' => 'Manage Services',
            'services' => $services,
            'search' => $search,
        ]);
    }

    public function createService(): View
    {
        return view('admin.services.create', [
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
        return view('admin.services.edit', [
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

    public function quotes(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $status = in_array($status, ['approved', 'pending'], true) ? $status : '';

        $quotes = QuoteRequest::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('service', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->when($status === 'approved', fn ($query) => $query->approved())
            ->when($status === 'pending', fn ($query) => $query->pending())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.quotes', [
            'pageTitle' => 'Quote Requests',
            'quotes' => $quotes,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function createQuote(): View
    {
        return view('admin.quotes.create', [
            'pageTitle' => 'Add Quote Request',
            'quote' => new QuoteRequest(['is_approved' => false]),
            'serviceOptions' => $this->quoteServiceOptions(),
        ]);
    }

    public function storeQuote(Request $request): RedirectResponse
    {
        QuoteRequest::create($this->validatedQuoteData($request));

        return redirect()
            ->route('admin.quotes')
            ->with('status', 'Quote request created successfully.');
    }

    public function editQuote(QuoteRequest $quote): View
    {
        return view('admin.quotes.edit', [
            'pageTitle' => 'Edit Quote Request',
            'quote' => $quote,
            'serviceOptions' => $this->quoteServiceOptions($quote->service),
        ]);
    }

    public function updateQuote(Request $request, QuoteRequest $quote): RedirectResponse
    {
        $quote->update($this->validatedQuoteData($request));

        return redirect()
            ->route('admin.quotes')
            ->with('status', 'Quote request updated successfully.');
    }

    public function updateQuoteApproval(Request $request, QuoteRequest $quote): RedirectResponse
    {
        $validated = $request->validate([
            'is_approved' => ['required', 'boolean'],
        ]);

        $quote->update([
            'is_approved' => (bool) $validated['is_approved'],
        ]);

        return redirect()
            ->route('admin.quotes')
            ->with('status', $quote->is_approved
                ? 'Quote request approved and now visible on the homepage.'
                : 'Quote request moved back to pending.');
    }

    public function destroyQuote(QuoteRequest $quote): RedirectResponse
    {
        $quote->delete();

        return redirect()
            ->route('admin.quotes')
            ->with('status', 'Quote request deleted successfully.');
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

        return view('admin.contacts', [
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

        return view('admin.users', [
            'pageTitle' => 'Manage Users',
            'users' => $users,
            'search' => $search,
        ]);
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
            ['label' => 'Quote', 'url' => route('quote'), 'icon' => 'fa-file-signature'],
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
                'label' => 'Quote Requests',
                'description' => 'View service requests saved from the quote form.',
                'url' => route('admin.quotes'),
                'icon' => 'fa-clipboard-list',
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

    /**
     * @return array{name: string, email: string, service: string, message: string, is_approved: bool}
     */
    private function validatedQuoteData(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150'],
            'service' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $validated['is_approved'] = $request->boolean('is_approved');

        return $validated;
    }

    /**
     * @return array<int, string>
     */
    private function quoteServiceOptions(?string $currentService = null): array
    {
        $services = Service::active()->oldest()->pluck('title')->all();

        if ($currentService !== null && $currentService !== '' && ! in_array($currentService, $services, true)) {
            $services[] = $currentService;
        }

        return $services !== []
            ? $services
            : ['IT Consultation', 'Web Development', 'App Development', 'Cyber Security'];
    }
}
