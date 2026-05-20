<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        $intendedUrl = $this->storeIntendedBookingUrl($request);

        return view('auth.login', [
            'pageTitle' => 'Login',
            'pageKey' => 'login',
            'intendedUrl' => $intendedUrl,
            'returnPath' => $this->displayPath($intendedUrl),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()
            ->intended($this->dashboardUrl($request->user()))
            ->with('status', 'Login successful.');
    }

    public function showRegister(Request $request): View
    {
        $intendedUrl = $this->storeIntendedBookingUrl($request);

        return view('auth.register', [
            'pageTitle' => 'Create Account',
            'pageKey' => 'register',
            'intendedUrl' => $intendedUrl,
            'returnPath' => $this->displayPath($intendedUrl),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => User::ROLE_USER,
            'is_admin' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->intended(route('user.dashboard'))
            ->with('status', 'Account created successfully. Login successful.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Logout successful.');
    }

    private function dashboardUrl(User $user): string
    {
        return route($user->isAdmin() ? 'admin.dashboard' : 'user.dashboard');
    }

    private function storeIntendedBookingUrl(Request $request): ?string
    {
        $redirect = $this->validBookingRedirect($request);

        if ($redirect !== null) {
            $request->session()->put('url.intended', $redirect);

            return $redirect;
        }

        $intended = $request->session()->get('url.intended');

        return is_string($intended) ? $intended : null;
    }

    private function validBookingRedirect(Request $request): ?string
    {
        $redirect = $request->query('redirect');

        if (! is_string($redirect)) {
            return null;
        }

        $redirect = trim($redirect);

        if ($redirect === '' || str_starts_with($redirect, '//')) {
            return null;
        }

        $redirectHost = parse_url($redirect, PHP_URL_HOST);

        if ($redirectHost !== null && $redirectHost !== $request->getHost()) {
            return null;
        }

        $redirectPath = parse_url($redirect, PHP_URL_PATH);
        $bookingPath = parse_url(route('booking'), PHP_URL_PATH);

        return $redirectPath === $bookingPath ? $redirect : null;
    }

    private function displayPath(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);

        if (! is_string($path) || $path === '') {
            return null;
        }

        return is_string($query) && $query !== '' ? "{$path}?{$query}" : $path;
    }
}
