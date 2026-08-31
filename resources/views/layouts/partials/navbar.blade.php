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

                <!-- Opsi Ubah Password Khusus Role Admin & Super Admin -->
                @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                    <button type="button" class="dropdown-item text-warning fw-medium" data-bs-toggle="modal" data-bs-target="#modalUbahPasswordAdmin">
                        <i class="fas fa-key me-2"></i> Ubah Password
                    </button>
                    <div class="dropdown-divider"></div>
                @endif

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

<!-- MODAL UBAH PASSWORD ADMIN -->
@auth
    @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
    <div class="modal fade" id="modalUbahPasswordAdmin" tabindex="-1" aria-labelledby="modalUbahPasswordAdminLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold fs-6" id="modalUbahPasswordAdminLabel">
                        <i class="fas fa-user-shield me-2"></i>Ubah Password Admin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="modal-body text-start p-4">
                        <!-- Password Saat Ini -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-medium small text-secondary">Password Saat Ini</label>
                            <div class="input-group">
                                <input type="password" 
                                       name="current_password" 
                                       id="current_password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       placeholder="Masukkan password lama" 
                                       required>
                                <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @error('current_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Password Baru -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-medium small text-secondary">Password Baru</label>
                            <div class="input-group">
                                <input type="password" 
                                       name="new_password" 
                                       id="new_password" 
                                       class="form-control @error('new_password') is-invalid @enderror" 
                                       placeholder="Minimal 8 karakter" 
                                       required>
                                <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @error('new_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label fw-medium small text-secondary">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" 
                                       name="new_password_confirmation" 
                                       id="new_password_confirmation" 
                                       class="form-control" 
                                       placeholder="Ulangi password baru" 
                                       required>
                                <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="new_password_confirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-warning fw-bold text-dark">
                            <i class="fas fa-save me-1"></i> Simpan Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Script Toggle Show / Hide Password
            document.querySelectorAll('.btn-toggle-password').forEach(function(button) {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });

            // Re-open Modal Secara Otomatis Jika Ada Error Validasi
            @if($errors->has('current_password') || $errors->has('new_password'))
                var modalAdmin = new bootstrap.Modal(document.getElementById('modalUbahPasswordAdmin'));
                modalAdmin.show();
            @endif
        });
    </script>
    @endif
@endauth