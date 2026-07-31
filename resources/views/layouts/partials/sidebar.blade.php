<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link text-decoration-none">
        <i class="fas fa-qrcode brand-image ms-3 mt-1 fa-lg text-primary"></i>
        <span class="brand-text font-weight-bold ms-2">E-Presensi</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                
                <!-- Menu Dashboard (Semua Role) -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Menu Scan Presensi -->
                <li class="nav-item">
                    <a href="{{ route('attendance.scan') }}" class="nav-link {{ request()->routeIs('attendance.scan') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-qrcode"></i>
                        <p>Scan Presensi QR</p>
                    </a>
                </li>

                <!-- Menu Khusus Admin / SuperAdmin -->
                @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                    <li class="nav-header">MANAJEMEN MASTER</li>
                    
                    <li class="nav-item">
                        <a href="{{ route('teachers.index') }}" class="nav-link {{ request()->routeIs('teachers.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Data Pegawai / Guru</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('work-schedules.index') }}" class="nav-link {{ request()->routeIs('work-schedules.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-clock"></i>
                            <p>Jam Kerja & Shift</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('shift-assignments.index') }}" class="nav-link {{ request()->routeIs('shift-assignments.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Penugasan Shift</p>
                        </a>
                    </li>

                    <li class="nav-header">REKAP & AUDIT</li>

                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-file-invoice"></i>
                            <p>Laporan Presensi</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-history"></i>
                            <p>Audit Logs</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Pengaturan Sekolah</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('settings.school.index') }}" class="nav-link {{ request()->routeIs('settings.school.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-map-marker-alt"></i>
                            <p>Lokasi Sekolah</p>
                        </a>
                    </li>
                @endif

            </ul>
        </nav>
    </div>
</aside>