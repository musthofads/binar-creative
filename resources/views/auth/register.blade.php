@extends('layouts.app')

@section('title', 'Register - Photobooth')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">
                        <i class="bi bi-person-plus"></i> Register
                    </h2>

                    <form id="registerForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="8">
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <div id="errorMsg" class="alert alert-danger d-none"></div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-person-plus"></i> Register
                        </button>
                    </form>

                    <div class="mt-3 text-center">
                        <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        const btn = e.target.querySelector('button[type="submit"]');
        const errorMsg = document.getElementById('errorMsg');

        // Check password match
        if (data.password !== data.password_confirmation) {
            errorMsg.textContent = 'Passwords do not match';
            errorMsg.classList.remove('d-none');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Registering...';
        errorMsg.classList.add('d-none');

        try {
            const response = await axios.post('/api/register', data);

            if (response.data.success) {
                window.location.href = '/camera';
            }
        } catch (error) {
            console.error('Registration error:', error);
            const errors = error.response?.data?.errors;
            if (errors) {
                const errorMessages = Object.values(errors).flat().join('<br>');
                errorMsg.innerHTML = errorMessages;
            } else {
                errorMsg.textContent = error.response?.data?.message || 'Registration failed. Please try again.';
            }
            errorMsg.classList.remove('d-none');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-person-plus"></i> Register';
        }
    });
</script>
@endsection
