@extends('layouts.app')

@section('title', 'Photobooth Gallery')

@section('styles')
    <style>
        .pagination {
            gap: 8px;
        }

        .pagination .page-link {
            border-radius: 10px;
            border: none;
            color: #555;
            padding: 8px 14px;
            transition: all 0.2s ease;
        }

        .pagination .page-link:hover {
            background-color: #f1f3f5;
            color: #000;
        }

        .pagination .active .page-link {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .pagination .disabled .page-link {
            opacity: 0.5;
        }

        /* Pastikan nav pagination Laravel menggunakan flex spread */
        .pagination-wrapper nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        /* Mengatur teks "Showing..." agar tetap berwarna terang (jika pakai dark mode) */
        .pagination-wrapper .text-muted {
            color: #ffffff !important;
            margin-bottom: 0;
        }

        /* Menghilangkan margin default dari ul pagination agar sejajar */
        .pagination-wrapper .pagination {
            margin-bottom: 0;
        }

        .input-group .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
            border: none;
        }

        /* Mempercantik tampilan mobile agar tidak berantakan */
        @media (max-width: 768px) {
            .input-group {
                width: 100%;
            }

            .d-flex.align-items-center.gap-3 {
                width: 100%;
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div class="d-flex flex-column">
                <h2 class="text-white mb-2">
                    <i class="bi bi-images"></i> Photobooth Gallery
                </h2>
                <div>
                    <span class="badge bg-light text-dark shadow-sm">
                        Total: {{ $sessions->total() }} Sessions
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <form action="{{ route('admin.gallery') }}" method="GET" class="d-flex align-items-center">
                    <div class="input-group shadow-sm">
                        <input type="text" name="search" class="form-control border-0" placeholder="Search customer..."
                               value="{{ request('search') }}" style="border-radius: 10px 0 0 10px; min-width: 200px;">
                        <button class="btn btn-primary" type="submit" style="border-radius: 0 10px 10px 0;">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>

                    @if (request('search'))
                        <a href="{{ route('admin.gallery') }}" class="btn btn-outline-light ms-2"
                           style="border-radius: 10px;">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="row g-4">
            @forelse($sessions as $session)
                <div class="col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="position-relative">
                            @if ($session->photos && $session->photos->isNotEmpty())
                                <img src="{{ $session->photos->first()->url }}" class="card-img-top" alt="First Photo"
                                     style="height: 200px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center text-muted"
                                     style="height: 200px;">
                                    <div class="text-center">
                                        <i class="bi bi-camera-video-off display-4"></i>
                                        <p class="small mb-0">No photos taken</p>
                                    </div>
                                </div>
                            @endif

                            <div class="position-absolute top-0 end-0 m-2">
                                @php
                                    $pkg = strtolower($session->package_type);
                                    $bgClass = 'bg-secondary';
                                    $icon = 'bi-person';

                                    if ($pkg == 'basic') {
                                        $bgClass = 'bg-info text-dark';
                                        $icon = 'bi-person'; // Icon 1 orang
                                    } elseif ($pkg == 'bestie') {
                                        $bgClass = 'bg-primary';
                                        $icon = 'bi-people'; // Icon 2 orang
                                    } elseif ($pkg == 'ramean') {
                                        $bgClass = 'bg-warning text-dark';
                                        $icon = 'bi-people-fill'; // Icon grup
                                    }
                                @endphp

                                <span class="badge rounded-pill {{ $bgClass }} shadow-sm px-3 py-2">
                                    <i class="bi {{ $icon }} me-1"></i> {{ strtoupper($session->package_type) }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title text-truncate mb-1">{{ $session->customer_name }}</h5>
                            <p class="card-text small text-muted mb-3">
                                <i class="bi bi-calendar3"></i> {{ $session->created_at->format('d M Y, H:i') }}
                            </p>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-primary fw-bold">
                                    <i class="bi bi-camera"></i> {{ $session->photo_count }} Photos
                                </div>
                                <div class="d-flex gap-1">
                                    @if ($session->qr_code_url)
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                                data-bs-target="#qrModal{{ $session->id }}">
                                            <i class="bi bi-qr-code"></i>
                                        </button>
                                    @endif

                                    <form action="{{ route('admin.gallery.destroy', $session->id) }}" method="POST"
                                          class="delete-session-form" data-id="{{ $session->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-top-0 d-grid pb-3">
                            <a href="{{ route('admin.gallery.show', $session->id) }}"
                               class="btn btn-primary btn-sm fw-bold">
                                <i class="bi bi-eye me-1"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>

                @if ($session->qr_code_url)
                    <div class="modal fade" id="qrModal{{ $session->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-sm">
                            <div class="modal-content text-center p-3">
                                <h6>QR Code: {{ $session->customer_name }}</h6>
                                <img src="{{ $session->qr_code_url }}" class="img-fluid rounded" alt="QR Code">
                                <button type="button" class="btn btn-secondary btn-sm mt-3"
                                        data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                @endif

            @empty
                <div class="col-12 text-center py-5 text-white">
                    <i class="bi bi-emoji-frown display-1 text-white"></i>
                    <p class="mt-3">Belum ada sesi photobooth yang tersimpan.</p>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-between align-items-center mt-5 mb-5 w-100">
            <div class="pagination-wrapper w-100">
                {{ $sessions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForms = document.querySelectorAll('.delete-session-form');

            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formElement = this;
                    const url = formElement.getAttribute('action');
                    const sessionId = formElement.getAttribute('data-id');
                    // Cari elemen card terdekat untuk dihapus nanti
                    const cardElement = formElement.closest('.col-md-4');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "All photos in this session will be gone forever!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6e7d88',
                        confirmButtonText: 'Yes, delete it!',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Tampilkan loading saat proses hapus
                            Swal.showLoading();

                            fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': formElement.querySelector(
                                            'input[name="_token"]').value,
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        _method: 'DELETE'
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Deleted!',
                                            text: data.message,
                                            timer: 1500,
                                            showConfirmButton: false
                                        });

                                        // Hapus card dari UI dengan efek fade out
                                        cardElement.style.transition = 'all 0.5s ease';
                                        cardElement.style.opacity = '0';
                                        cardElement.style.transform = 'scale(0.9)';

                                        setTimeout(() => {
                                            cardElement.remove();
                                            // Opsional: Cek jika sudah tidak ada card, tampilkan pesan "Empty"
                                            if (document.querySelectorAll(
                                                    '.photo-item').length ===
                                                0) {
                                                location.reload();
                                            }
                                        }, 500);
                                    } else {
                                        Swal.fire('Error!', data.message, 'error');
                                    }
                                })
                                .catch(error => {
                                    Swal.fire('Error!', 'Network error or server down.',
                                        'error');
                                });
                        }
                    });
                });
            });
        });
    </script>
@endsection
