@extends('layouts.app')

@section('title', 'Session Details - ' . $session->customer_name)

@section('styles')
    <style>
        .qr-wrapper {
            background: #fff;
            padding: 12px;
            border-radius: 16px;
            border: 1px solid #eee;
            width: fit-content;
        }

        .qr-wrapper img {
            max-width: 170px;
        }

        /* PIN BOX */
        .pin-box {
            width: 48px;
            height: 58px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .pin-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
        }

        /* TOGGLE */
        .toggle-pin {
            cursor: pointer;
            color: #6c757d;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-pin:hover {
            color: #0d6efd;
        }
    </style>
@endsection

@section('content')
    <div class="container pb-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                {{-- Link to Gallery --}}
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.gallery') }}" class="text-white text-decoration-none opacity-75">
                        Gallery
                    </a>
                </li>
                {{-- Active Session Name --}}
                <li class="breadcrumb-item active text-white fw-bold" aria-current="page">
                    {{ $session->customer_name }}
                </li>
            </ol>
        </nav>

        <style>
            /* This ensures the "/" separator also turns white */
            .breadcrumb-item+.breadcrumb-item::before {
                color: rgba(255, 255, 255, 0.5) !important;
            }
        </style>

        <div class="row">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h4 class="fw-bold mb-3">Customer Information</h4>
                        <hr>

                        <div class="mb-3">
                            <label class="text-muted d-block small">Customer Name</label>
                            <span class="fw-bold fs-5 text-dark">{{ $session->customer_name }}</span>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted d-block small mb-2">Package Type</label>
                            @php
                                $pkg = strtolower($session->package_type);
                                $color = $pkg == 'basic' ? 'info' : ($pkg == 'bestie' ? 'primary' : 'warning');
                                $icon =
                                    $pkg == 'basic' ? 'bi-person' : ($pkg == 'bestie' ? 'bi-people' : 'bi-people-fill');
                            @endphp
                            <span class="badge rounded-pill bg-{{ $color }} px-3 py-2 text-uppercase shadow-sm">
                                <i class="bi {{ $icon }} me-1"></i> {{ $session->package_type }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted d-block small">Session Date</label>
                            <span class="text-dark">
                                <i
                                   class="bi bi-calendar-check me-2 text-primary"></i>{{ $session->created_at->format('M d, Y') }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted d-block small">Time</label>
                            <span class="text-dark">
                                <i class="bi bi-clock me-2 text-primary"></i>{{ $session->created_at->format('H:i A') }}
                            </span>
                        </div>

                        <div class="mb-0">
                            <label class="text-muted d-block small">Session ID</label>
                            <small class="font-monospace text-secondary">{{ $session->session_id }}</small>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm text-center p-4 rounded-4">

                    <!-- TITLE -->
                    <label class="text-muted small text-uppercase fw-semibold mb-3">
                        Session QR Code
                    </label>

                    <!-- QR -->
                    <div class="qr-wrapper mx-auto mb-3">
                        <img src="{{ $qrCodeData }}" class="img-fluid" alt="QR Code">
                    </div>

                    <!-- DESCRIPTION -->
                    <p class="small text-muted mb-2">
                        Scan or share this QR to access the photos
                    </p>

                    <!-- LINK -->
                    <div class="text-center mb-3">
                        <a href="{{ route('public.gallery.show', $session->session_id) }}" target="_blank"
                           class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-link-45deg me-1"></i> Open Public Link
                        </a>
                    </div>

                    <!-- PIN -->
                    <div class="pin-section mt-2">
                        <label class="text-muted small fw-semibold mb-2 d-block">
                            <i class="bi bi-shield-lock me-1"></i> Access PIN
                        </label>

                        <div class="d-flex justify-content-center gap-2 mb-2">
                            @foreach (str_split($session->access_password) as $digit)
                                <input type="password" class="pin-box" value="{{ $digit }}" readonly>
                            @endforeach
                        </div>

                        <div class="toggle-pin" onclick="togglePin()">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                            <span class="small ms-1">Show PIN</span>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 fw-bold text-white">
                        <i class="bi bi-camera me-2 text-white"></i>Photo Collection
                    </h4>
                    <span class="badge bg-white text-dark border shadow-sm px-3">
                        {{ $session->photos->count() }} Images
                    </span>
                </div>

                <div class="row g-3" id="photo-container">
                    @include('partials.photo-items')
                </div>

                @if ($photos->hasMorePages())
                    <div class="text-center mt-3">
                        <button id="loadMoreBtn" class="btn btn-primary">
                            Load More
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .photo-card {
            transition: all 0.3s ease;
        }

        .photo-card:hover {
            transform: translateY(-8px);
            shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .border-dashed {
            border-style: dashed !important;
            border-width: 2px !important;
        }
    </style>
@endsection

@section('scripts')
    <script>
        function togglePin() {
            const inputs = document.querySelectorAll('.pin-box');
            const icon = document.getElementById('toggleIcon');

            inputs.forEach(input => {
                input.type = input.type === 'password' ? 'text' : 'password';
            });

            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const imageModal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');

            imageModal.addEventListener('show.bs.modal', function(event) {
                // Ambil URL gambar dari data-bs-src trigger
                const button = event.relatedTarget;
                const src = button.getAttribute('data-bs-src');
                modalImage.src = src;
            });

            // Bersihkan src saat modal ditutup agar tidak berat
            imageModal.addEventListener('hidden.bs.modal', function() {
                modalImage.src = '';
            });
        });
    </script>
    <script>
        let page = 1;
        const btn = document.getElementById('loadMoreBtn');

        if (btn) {
            btn.addEventListener('click', function() {
                page++;

                btn.innerText = 'Loading...';
                btn.disabled = true;

                fetch(`?page=${page}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {

                        document
                            .getElementById('photo-container')
                            .insertAdjacentHTML('beforeend', data.html);

                        if (!data.hasMore) {
                            btn.style.display = 'none';
                        }

                        btn.innerText = 'Load More';
                        btn.disabled = false;
                    })
                    .catch(() => {
                        btn.innerText = 'Load More';
                        btn.disabled = false;
                    });
            });
        }
    </script>
@endsection
