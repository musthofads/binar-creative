@extends('layouts.app') {{-- Gunakan layout tanpa sidebar admin --}}

@section('content')
    <div class="container py-4">
        <div class="text-center text-white mb-4">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo"
                 style="max-height: 120px; width: auto; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));">
            <h4 class="fw-bold">Hi, {{ $session->customer_name }}!</h4>
            <p class="">Your photobooth memories are ready to download.</p>
        </div>

        <div class="row g-3 justify-content-center">
            @foreach ($session->photos as $photo)
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <img src="{{ asset('storage/' . $photo->storage_path) }}" class="card-img-top img-preview"
                             onclick="previewImage('{{ asset('storage/' . $photo->storage_path) }}')"
                             style="height: 200px; object-fit: cover; cursor: pointer;">
                        <div class="card-body p-2">
                            <a href="{{ asset('storage/' . $photo->storage_path) }}"
                               download="Photo_{{ $loop->iteration }}.jpg" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-5">
            <p class="small text-white">&copy; {{ date('Y') }} Photobox by {{ config('app.name') }}</p>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function previewImage(src) {
            const modalImg = document.getElementById('modalPreviewImage');
            const modalElement = document.getElementById('previewModal');

            if (modalImg && modalElement) {
                modalImg.src = src;

                let modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalElement, {
                        keyboard: true,
                        backdrop: true
                    });
                }
                modalInstance.show();
            }
        }
    </script>
@endsection
