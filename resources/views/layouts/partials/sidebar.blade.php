@php
    $userRole = auth()->user()->role ?? '';
    
    // Tentukan link Logo E-Presensi berdasarkan role
    $brandRoute = match($userRole) {
        'petugas' => route('attendance.scan'),
        'guru'    => route('attendance.my-history'),
        default   => route('dashboard'),
    };
@endphp

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ $brandRoute }}" class="brand-link text-decoration-none">
        <i class="fas fa-qrcode brand-image ms-3 mt-1 fa-lg text-primary"></i>
        <span class="brand-text font-weight-bold ms-2">E-Presensi</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                
                <!-- 1. Menu Dashboard (SuperAdmin, Admin, Kepsek, Wakakur) -->
                @if(in_array($userRole, ['super_admin', 'admin', 'kepala_sekolah', 'wakil_kurikulum']))
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endif

                <!-- 2. Menu Scan Presensi (SuperAdmin, Admin, Petugas) -->
                @if(in_array($userRole, ['super_admin', 'admin', 'petugas']))
                    <li class="nav-item">
                        <a href="{{ route('attendance.scan') }}" class="nav-link {{ request()->routeIs('attendance.scan') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-qrcode"></i>
                            <p>Scan Presensi QR</p>
                        </a>
                    </li>
                @endif

                <!-- 3. Menu Riwayat Presensi Saya (Khusus Role Guru) -->
                @if($userRole === 'guru')
                    <li class="nav-item">
                        <a href="{{ route('attendance.my-history') }}" class="nav-link {{ request()->routeIs('attendance.my-history') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-history text-info"></i>
                            <p>Riwayat Presensi Saya</p>
                        </a>
                    </li>
                @endif

                <!-- 4. MANAJEMEN MASTER -->
                @if(in_array($userRole, ['super_admin', 'admin', 'kepala_sekolah', 'wakil_kurikulum']))
                    <li class="nav-header">MANAJEMEN MASTER</li>
                    
                    <!-- Data Pegawai / Guru (Bisa diakses Admin, Kepsek, Wakakur) -->
                    <li class="nav-item">
                        <a href="{{ route('teachers.index') }}" class="nav-link {{ request()->routeIs('teachers.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Data Pegawai / Guru</p>
                        </a>
                    </li>

                    <!-- Jam Kerja & Shift (Khusus SuperAdmin & Admin) -->
                    @if(in_array($userRole, ['super_admin', 'admin']))
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
                    @endif

                    <!-- 5. REKAP & PENGATURAN -->
                    <li class="nav-header">REKAP & PENGATURAN</li>

                    <!-- Laporan Presensi (SuperAdmin, Admin, Kepsek, Wakakur) -->
                    <li class="nav-item">
                        <a href="{{ route('reports.attendance') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Laporan Presensi</p>
                        </a>
                    </li>

                    <!-- Pengaturan Sistem (Khusus SuperAdmin & Admin) -->
                    @if(in_array($userRole, ['super_admin', 'admin']))
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-history"></i>
                                <p>Audit Logs</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('settings.school.index') }}" class="nav-link {{ request()->routeIs('settings.school.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-map-marker-alt"></i>
                                <p>Pengaturan Lokasi</p>
                            </a>
                        </li>
                    @endif
                @endif

            </ul>
        </nav>
    </div>
</aside>