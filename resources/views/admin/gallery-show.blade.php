@extends('layouts.app')

@section('title', 'Session Details - ' . $session->customer_name)

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

                @if ($session->qr_code_url)
                    <div class="card shadow-sm border-0 text-center p-4">
                        <label class="text-muted d-block small mb-3 text-uppercase fw-bold">Session QR Code</label>
                        <div class="bg-white p-2 border rounded d-inline-block mx-auto">
                            <img src="{{ $session->qr_code_url }}" class="img-fluid" style="max-width: 180px;"
                                 alt="QR Code">
                        </div>
                        <p class="small text-muted mt-3 mb-0">Clients can scan this to access their private gallery online.
                        </p>
                    </div>
                @endif
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
            btn.addEventListener('click', function () {
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
