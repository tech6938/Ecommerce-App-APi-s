<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ $setting->company_name ?? 'Otika' }} - Admin Dashboard</title>

    <link rel="stylesheet" href="{{ asset('assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <link rel='shortcut icon' type='image/x-icon'
        href="{{ $setting?->favicon ? asset('systemsetting/' . $setting->favicon) : asset('assets/img/favicon.ico') }}" />

    @yield('css')
</head>

<body>
    <div class="loader"></div>
    <x-sweet-alert />

    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>

            <nav class="navbar navbar-expand-lg main-navbar sticky">
                <div class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li>
                            <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn">
                                <i data-feather="align-justify"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link nav-link-lg fullscreen-btn">
                                <i data-feather="maximize"></i>
                            </a>
                        </li>
                        <li>
                            <form class="form-inline mr-auto">
                                <div class="search-element">
                                    <input class="form-control" type="search" placeholder="Search" aria-label="Search"
                                        data-width="200">
                                    <button class="btn" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </li>
                    </ul>
                </div>

                <ul class="navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown"
                            class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <img alt="image" src="{{ asset('assets/img/user.png') }}" class="user-img-radious-style">
                            <span class="d-sm-none d-lg-inline-block"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right pullDown">
                            <a href="{{ route('system.setting') }}" class="dropdown-item has-icon">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" id="navbarLogoutBtn" class="dropdown-item has-icon text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">

                    <div class="sidebar-brand">
                        <a href="{{ route('dashboard') }}">
                            <img alt="image" style="height: 100px; width:200px;"
                                src="{{ $setting?->logo ? asset('systemsetting/' . $setting->logo) : asset('assets/img/logo.png') }}"
                                class="header-logo" />

                        </a>
                    </div>

                    <ul class="sidebar-menu">
                        <li class="menu-header">Main</li>

                        <li class="dropdown {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}" class="nav-link">
                                <i data-feather="monitor"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="dropdown {{ request()->routeIs('banner.*') ? 'active' : '' }}">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                                <i data-feather="image"></i>
                                <span>Banners</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('banner.index') }}">Banner List</a></li>
                            </ul>
                        </li>

                        <li class="dropdown {{ request()->routeIs('category.*') ? 'active' : '' }}">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                                <i data-feather="grid"></i>
                                <span>Category</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('category.index') }}">Category List</a></li>
                            </ul>
                        </li>
                        <li class="dropdown {{ request()->routeIs('products.*') ? 'active' : '' }}">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                                <i data-feather="package"></i>
                                <span>Products</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('products.index') }}">Product List</a></li>
                            </ul>
                        </li>

                        <li class="dropdown {{ request()->routeIs('currency.*') ? 'active' : '' }}">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                                <i data-feather="dollar-sign"></i>
                                <span>Currency</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('currency.index') }}">Currency List</a></li>
                            </ul>
                        </li>

                        <li class="dropdown {{ request()->routeIs('coupon.*') ? 'active' : '' }}">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                                <i data-feather="tag"></i>
                                <span>Coupons</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('coupon.index') }}">Coupon List</a></li>
                            </ul>
                        </li>

                        <li class="dropdown {{ request()->routeIs('cod-charges.*') ? 'active' : '' }}">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                                <i data-feather="dollar-sign"></i>
                                <span>COD Charges</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('cod-charges.index') }}">COD Charge List</a>
                                </li>
                            </ul>
                        </li>

                        <li class="dropdown {{ request()->routeIs('admin.chat.*') ? 'active' : '' }}">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                               <i data-feather="message-square"></i>
                                <span>Chat</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('admin.chat.index') }}">Chat</a>
                                </li>
                            </ul>
                        </li>
                        {{-- <li class="dropdown
    {{ request()->routeIs('attributes.*') || request()->routeIs('category.attributes.*') ? 'active' : '' }}">

    <a href="#" class="menu-toggle nav-link has-dropdown">
        <i data-feather="sliders"></i>
        <span>Product Setup</span>
    </a>

    <ul class="dropdown-menu">

        <li class="{{ request()->routeIs('attributes.*') ? 'active' : '' }}">
            <a class="nav-link"
               href="{{ route('attributes.index') }}">
                Attributes
            </a>
        </li>

        <li class="{{ request()->routeIs('category.attributes.*') ? 'active' : '' }}">
            <a class="nav-link"
               href="{{ route('category.attributes.index') }}">
                Assign Attributes
            </a>
        </li>

    </ul>

</li> --}}

                        <li class="dropdown {{ request()->routeIs('system.setting') ? 'active' : '' }}">
                            <a href="{{ route('system.setting') }}" class="nav-link">
                                <i data-feather="settings"></i>
                                <span>System Setting</span>
                            </a>
                        </li>

                        <li class="menu-header">OFF LOAD</li>

                        <li class="dropdown">
                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                style="display:none;">
                                @csrf
                            </form>
                            <a href="#" id="logoutBtn" class="nav-link has-icon text-danger">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="menu-text">Logout</span>
                            </a>
                        </li>

                    </ul>
                </aside>
            </div>

            @yield('content')

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/index.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    @yield('js')

    <script>
        // Sidebar logout
        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, logout!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        });

        // Navbar logout
        document.getElementById('navbarLogoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, logout!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        });
    </script>

</body>

</html>
