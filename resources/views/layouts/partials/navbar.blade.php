<nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0 shadow-sm">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <span class="nav-link fw-semibold text-dark">SMK Syafi'i Akrom</span>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ms-auto align-items-center">
        <!-- User Profile Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" href="#">
                <i class="fas fa-user-circle fa-lg text-secondary"></i>
                <span class="d-none d-md-inline fw-semibold">{{ auth()->user()->email }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow">
                <div class="dropdown-header">
                    <strong>Role:</strong> {{ strtoupper(auth()->user()->role) }}
                </div>
                <div class="dropdown-divider"></div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Keluar
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>