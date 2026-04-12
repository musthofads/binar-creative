@extends('layouts.app')

@section('title', 'Login - Photobooth')

@section('content')
<div class="container">
    <div class="text-center my-3"> <img src="{{ asset('assets/images/logo.png') }}"
            alt="Logo"
            style="max-height: 120px; width: auto; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));">
    </div>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </h2>

                    <form id="loginForm" class="text-start mb-3">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <div id="errorMsg" class="alert alert-danger d-none"></div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                    </form>

                    {{-- <div class="mt-3 text-center">
                        <p>Don't have an account? <a href="{{ route('register') }}">Register here</a></p>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        const btn = e.target.querySelector('button[type="submit"]');
        const errorMsg = document.getElementById('errorMsg');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Logging in...';
        errorMsg.classList.add('d-none');

        try {
            // GANTI DISINI: URL tanpa awalan /api
            const response = await axios.post('/login', data);

            if (response.data.success) {
                // Karena session sudah tersimpan di browser, redirect akan aman
                window.location.href = response.data.user.role === 'SUPERADMIN'
                    ? '/admin/dashboard'
                    : '/camera';
            }
        } catch (error) {
            console.error('Login error:', error);
            // Tangkap pesan error dari Controller
            const message = error.response?.data?.message || 'Login failed. Please try again.';
            errorMsg.textContent = message;
            errorMsg.classList.remove('d-none');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Login';
        }
    });
</script>
@endsection
