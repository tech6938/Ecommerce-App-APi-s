<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ $setting->company_name ?? 'Otika' }} - Login</title>

    <link rel="stylesheet" href="{{ asset('assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    <link rel='shortcut icon' type='image/x-icon'
        href="{{ $setting?->favicon ? asset('systemsetting/' . $setting->favicon) : asset('assets/img/favicon.ico') }}" />
</head>

<body>
    <div class="loader"></div>

    <div id="app">
        <section class="section">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">

                        <div class="card card-primary">

                            {{-- Card Header with Logo --}}
                            <div class="card-header text-center d-flex flex-column align-items-center py-4">
                                <img
                                    src="{{ $setting?->logo ? asset('systemsetting/' . $setting->logo) : asset('assets/img/logo.png') }}"
                                    alt="{{ $setting->company_name ?? 'Otika' }}"
                                    style="height: 50px; object-fit: contain; margin-bottom: 10px;">
                                <h4 class="mb-0">{{ $setting->company_name ?? 'Otika' }}</h4>
                                <p class="text-muted mb-0" style="font-size: 13px;">Sign in to your account</p>
                            </div>

                            <div class="card-body">

                                {{-- Error Message --}}
                                @if(session('error'))
                                    <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        <span>{{ session('error') }}</span>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('match-login') }}" class="needs-validation" novalidate>
                                    @csrf

                                    {{-- Email --}}
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input
                                            id="email"
                                            type="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            class="form-control {{ $errors->has('email') ? 'border border-danger' : '' }}"
                                            placeholder="Enter your email"
                                            tabindex="1"
                                            required
                                            autofocus>
                                        @error('email')
                                            <small class="text-danger">
                                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                            </small>
                                        @enderror
                                    </div>

                                    {{-- Password --}}
                                    <div class="form-group">
                                        <div class="d-flex justify-content-between">
                                            <label for="password">Password</label>
                                            <a href="{{ route('forget') }}" class="text-small text-primary" style="font-size: 13px;">
                                                Forgot Password?
                                            </a>
                                        </div>
                                        <div class="input-group">
                                            <input
                                                id="password"
                                                type="password"
                                                name="password"
                                                class="form-control {{ $errors->has('password') ? 'border border-danger' : '' }}"
                                                placeholder="Enter your password"
                                                tabindex="2"
                                                required>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @error('password')
                                            <small class="text-danger">
                                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                            </small>
                                        @enderror
                                    </div>

                                    {{-- Remember Me --}}
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="remember" class="custom-control-input" tabindex="3" id="remember-me">
                                            <label class="custom-control-label" for="remember-me">Remember Me</label>
                                        </div>
                                    </div>

                                    {{-- Submit --}}
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
                                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                                        </button>
                                    </div>

                                </form>

                            </div>

                            {{-- Card Footer --}}
                            <div class="card-footer text-center text-muted" style="font-size: 12px;">
                                &copy; {{ date('Y') }} {{ $setting->company_name ?? 'Otika' }}. All rights reserved.
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    <script>
        // Password show/hide toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>

</body>
</html>