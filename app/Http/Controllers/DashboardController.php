<?php

namespace App\Http\Controllers;

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
        return view('dashboard.user', [
            'user' => $request->user(),
            'pageTitle' => 'User Dashboard',
            'quickLinks' => $this->quickLinks(),
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function quickLinks(): array
    {
        return [
            [
                'label' => 'Request a Quote',
                'description' => 'Send a project requirement directly from your account.',
                'icon' => 'fa-file-signature',
                'url' => route('quote'),
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
