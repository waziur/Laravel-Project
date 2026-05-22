@extends('master.master')

@section('content')
    <div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row g-5 align-items-stretch justify-content-center">
                <div class="col-lg-5">
                    <div class="auth-panel h-100">
                        <div class="section-title position-relative pb-3 mb-4">
                            <h5 class="fw-bold text-primary text-uppercase">Welcome Back</h5>
                            <h1 class="mb-0">Login to your panel</h1>
                        </div>

                        <form action="{{ route('login.store') }}" method="post" data-toast-pending="Checking your login details...">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control bg-light border-0 px-4 @error('email') is-invalid @enderror" style="height: 55px;" required autofocus>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control bg-light border-0 px-4 @error('password') is-invalid @enderror" style="height: 55px;" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                            </div>
                            <button class="btn btn-primary w-100 py-3" type="submit">Login</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="auth-side auth-create-card h-100">
                        <div class="auth-create-content">
                            <span class="auth-side-icon"><i class="fa fa-user-plus"></i></span>
                            <span class="auth-create-label">New here?</span>
                            <h2 class="text-white mb-3">Create your account</h2>
                            <p class="text-white-50 mb-4">Start fresh with a simple account and enter your dashboard instantly.</p>
                            <a href="{{ route('register', $intendedUrl ? ['redirect' => $intendedUrl] : []) }}" class="auth-register-button">
                                <i class="fa fa-user-plus me-2"></i>Create New Account
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
