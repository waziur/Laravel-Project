@extends('master.master')

@section('content')
    <div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row g-5 align-items-stretch justify-content-center">
                <div class="col-lg-5">
                    <div class="auth-side h-100">
                        <span class="auth-side-icon"><i class="fa fa-user-plus"></i></span>
                        <h2 class="text-white mb-3">Create your Startup account</h2>
                        <p class="text-white-50 mb-4">After registration you will enter the requested page automatically.</p>
                        <div class="auth-flow">
                            <div><i class="fa fa-check-circle"></i><span>Secure login</span></div>
                            <div><i class="fa fa-columns"></i><span>User dashboard</span></div>
                            <div><i class="fa fa-headset"></i><span>Service access</span></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="auth-panel h-100">
                        <div class="section-title position-relative pb-3 mb-4">
                            <h5 class="fw-bold text-primary text-uppercase">New Account</h5>
                            <h1 class="mb-0">Register as user</h1>
                        </div>

                        <form action="{{ route('register.store') }}" method="post" data-toast-pending="Creating your account...">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Full name</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control bg-light border-0 px-4 @error('name') is-invalid @enderror" style="height: 55px;" required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control bg-light border-0 px-4 @error('email') is-invalid @enderror" style="height: 55px;" required>
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
                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Confirm password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control bg-light border-0 px-4" style="height: 55px;" required>
                            </div>
                            <button class="btn btn-primary w-100 py-3" type="submit">Create Account</button>
                            <p class="mb-0 mt-4 text-center">Already have an account? <a href="{{ route('login', $intendedUrl ? ['redirect' => $intendedUrl] : []) }}" class="text-primary">Login</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
