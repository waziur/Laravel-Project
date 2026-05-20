<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureBookingRequesterIsAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $request->session()->put('url.intended', $request->fullUrl());

        return redirect()
            ->route('login', ['redirect' => $request->fullUrl()])
            ->with('auth_notice', 'Please log in or create an account to submit a booking.');
    }
}
