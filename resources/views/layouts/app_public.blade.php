<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Photobox by Binar')</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            /* Menambahkan pattern di atas gradasi */
            background:
                url('{{ asset('assets/images/pattern.png') }}'),
                linear-gradient(135deg, #667eea 0%, #764ba2 100%);

            /* Pengaturan agar pattern berulang dengan cantik */
            background-repeat: repeat;
            background-size: auto;
            /* atau atur px jika pattern terlalu besar, misal: 200px */
            background-blend-mode: overlay;
            /* Opsional: menyatukan pattern dengan warna di bawahnya */

            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            font-weight: 500;
            color: #4a5568 !important;
            transition: all 0.3s ease;
            position: relative;
            padding: 0.5rem 1rem !important;
            margin: 0 0.25rem;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 80%;
        }

        .nav-link:hover {
            color: #667eea !important;
        }

        .content-wrapper {
            padding: 2rem 0;
            position: relative;
            z-index: 1;
        }

        .card {
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            border: none;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.25);
        }

        .btn {
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            /* Gradasi dari Biru Cerah ke Biru Tua (Deep Blue) */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            /* Shadow biru yang lembut */
            box-shadow: 0 4px 15px rgba(37, 117, 252, 0.4);
            border: none;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-primary:hover {
            /* Warna sedikit lebih elektrik saat di-hover */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 6px 20px rgba(37, 117, 252, 0.6);
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            box-shadow: 0 4px 15px rgba(72, 187, 120, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            box-shadow: 0 4px 15px rgba(237, 137, 54, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
            box-shadow: 0 4px 15px rgba(245, 101, 101, 0.4);
        }

        .btn-info {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            box-shadow: 0 4px 15px rgba(66, 153, 225, 0.4);
        }

        /* Loading Animation */
        .spinner-border {
            border-width: 3px;
        }

        /* Smooth Transitions */
        * {
            transition: all 0.3s ease;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        /* Menghilangkan scrollbar saat modal muncul */
        .modal-open {
            overflow: hidden;
        }

        .modal-backdrop.show {
            opacity: 0.75;
            /* Default bootstrap biasanya 0.5 */
            background-color: #000;
        }

        #previewModal {
            z-index: 9999 !important;
            background: rgba(0, 0, 0, 0.9);
            /* Overlay semi transparan */
        }

        /* Memastikan gambar tidak pecah tapi tetap memenuhi area */
        #modalPreviewImage {
            transition: transform 0.3s ease;
        }

        /* Animasi masuk modal yang lebih smooth */
        .modal.fade .modal-dialog {
            transform: scale(0.9);
            transition: transform 0.3s ease-out;
        }

        .modal.show .modal-dialog {
            transform: scale(1);
        }

        .btn-close-white {
            background-color: rgba(0, 0, 0, 0.3);
            /* Memberikan lingkaran hitam transparan */
            border-radius: 50%;
            padding: 10px;
        }

        /* Styling Khusus Modal */
        #sessionModal .modal-content {
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        #sessionModal .modal-header {
            /* Gradient yang selaras dengan tema video wrapper sebelumnya */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 25px 25px 0 0;
            border-bottom: none;
        }

        /* Responsivitas Paket (Radio Buttons) */
        .btn-check + .btn-outline-primary {
            border-color: #e2e8f0;
            color: #4a5568;
            background: white;
            transition: all 0.3s ease;
            border-width: 2px;
        }

        .btn-check:checked + .btn-outline-primary {
            background: #667eea;
            border-color: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        /* Media Query untuk Perangkat Kecil (HP) */
        @media (max-width: 576px) {
            #sessionModal .modal-dialog {
                margin: 1rem; /* Memberi jarak agar tidak nempel layar */
            }

            #sessionModal .modal-body {
                padding: 1.5rem !important;
            }

            /* Membuat pilihan paket lebih mudah diklik di HP */
            .btn-outline-primary strong {
                font-size: 0.9rem;
            }
            .btn-outline-primary small {
                font-size: 0.7rem;
            }

            #sessionModal .modal-title {
                font-size: 1.1rem;
            }
        }

        /* Untuk iPad/Tablet agar modal tidak terlalu kurus */
        @media (min-width: 768px) {
            #sessionModal .modal-dialog {
                max-width: 500px;
            }
        }

        /* Container Package */
        .package-options .col-4 {
            padding: 0 4px; /* Rapatkan sedikit jarak antar kolom di HP */
        }

        /* Styling Label */
        .btn-check + .btn-outline-primary {
            border-radius: 15px;
            border: 2px solid #e2e8f0;
            background-color: #f8fafc;
            color: #475569;
            padding: 15px 5px !important; /* Padding atas-bawah cukup, kiri-kanan minimal */
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: all 0.2s ease-in-out;
        }

        /* Teks di dalam Label */
        .btn-check + .btn-outline-primary strong {
            font-size: 0.95rem;
            display: block;
            line-height: 1.2;
        }

        .btn-check + .btn-outline-primary small {
            font-size: 0.75rem;
            opacity: 0.8;
            margin-top: 4px;
            display: block;
            white-space: nowrap; /* Mencegah teks jumlah orang turun berantakan */
        }

        /* Efek Hover & Active */
        .btn-check:checked + .btn-outline-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(102, 126, 234, 0.25);
        }

        /* Responsive Focus: Mobile (HP Sangat Kecil) */
        @media (max-width: 375px) {
            .btn-check + .btn-outline-primary strong {
                font-size: 0.8rem;
            }
            .btn-check + .btn-outline-primary small {
                font-size: 0.65rem;
            }
            .btn-check + .btn-outline-primary {
                padding: 10px 2px !important;
            }
        }

        /* Responsive Focus: Tablet & Laptop */
        @media (min-width: 768px) {
            .btn-check + .btn-outline-primary {
                padding: 20px 10px !important;
            }
            .btn-check + .btn-outline-primary strong {
                font-size: 1.1rem;
            }
        }

        /* Styling Modal Header */
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: none;
            padding: 1.5rem 1rem;
        }

        /* Judul Modal yang Responsif */
        .modal-title {
            font-size: 1.1rem; /* Ukuran default untuk mobile */
            line-height: 1.4;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap; /* Agar bintang bisa turun jika layar terlalu sempit */
            gap: 8px;
        }

        /* Animasi halus untuk ikon bintang */
        .modal-title i {
            color: #ffd700;
            font-size: 1rem;
        }

        /* Sub-label di bawah judul */
        .modal-header small {
            display: block;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        /* Media Query: Tablet & Desktop */
        @media (min-width: 768px) {
            .modal-title {
                font-size: 1.5rem; /* Lebih besar di layar desktop */
            }

            .modal-title i {
                font-size: 1.3rem;
            }

            .modal-header small {
                font-size: 0.95rem;
            }
        }

        /* Khusus Mobile Sangat Kecil (iPhone SE / Fold) */
        @media (max-width: 350px) {
            .modal-title {
                font-size: 0.95rem;
            }
            .modal-title i {
                display: none; /* Sembunyikan bintang di layar super sempit agar teks utama muat */
            }
        }
    </style>

    @yield('styles')
</head>

<body>
    <!-- Content -->
    <div class="content-wrapper">
        @yield('content')
    </div>


    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content border-0 bg-dark">
                <div class="modal-body p-0 d-flex align-items-center justify-content-center position-relative">

                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-4"
                            data-bs-dismiss="modal" aria-label="Close" style="z-index: 10; transform: scale(1.2);">
                    </button>

                    <img src="" id="modalPreviewImage" class="img-fluid" alt="Preview"
                         style="width: 100%; height: 100%; object-fit: contain;">

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="sessionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white border-0">
                    <div class="w-100 text-center py-2 px-1">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-stars"></i>
                            <span>Welcome to Photobox by Binar</span>
                            <i class="bi bi-stars"></i>
                        </h5>
                        <small class="opacity-75 d-block">
                            Let's capture your beautiful moments!
                        </small>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <form id="startSessionForm">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Customer Name</label>
                            <input type="text" id="customerName" class="form-control form-control-lg"
                                   placeholder="Enter your name">
                            <div id="nameError" class="text-danger mt-2 d-none" style="font-size: 0.85rem;">
                                <i class="bi bi-exclamation-circle-fill me-1"></i> Please enter your name first.
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-3">Choose Package</label>
                            <div class="row g-2 package-options"> <div class="col-4">
                                    <input type="radio" class="btn-check" name="package" id="pkg_basic" value="basic" checked>
                                    <label class="btn btn-outline-primary w-100" for="pkg_basic">
                                        <strong>Basic</strong>
                                        <small>1 People</small>
                                    </label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="package" id="pkg_bestie" value="bestie">
                                    <label class="btn btn-outline-primary w-100" for="pkg_bestie">
                                        <strong>Bestie</strong>
                                        <small>2 People</small>
                                    </label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="package" id="pkg_ramean" value="ramean">
                                    <label class="btn btn-outline-primary w-100" for="pkg_ramean">
                                        <strong>Ramean</strong>
                                        <small>3+ People</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                            <i class="bi bi-camera-fill me-2"></i>Open Camera
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body p-0 position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-4"
                            data-bs-dismiss="modal" aria-label="Close" style="z-index: 10; transform: scale(1.2);">
                    </button>

                    <img src="" id="modalImage" class="img-fluid" alt="Preview"
                         style="width: 100%; height: 100%; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Setup axios defaults
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

        async function logout() {
            try {
                // Melakukan request POST ke /logout
                await axios.post('/logout');
                // Redirect ke halaman login atau home setelah logout berhasil
                window.location.href = '/login';
            } catch (error) {
                console.error('Logout failed:', error);
                // Jika gagal (misal session expired), paksa pindah halaman
                window.location.href = '/login';
            }
        }

        // Add ripple effect to buttons
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function(e) {
                let ripple = document.createElement('span');
                ripple.classList.add('ripple');
                this.appendChild(ripple);

                let x = e.clientX - e.target.offsetLeft;
                let y = e.clientY - e.target.offsetTop;

                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;

                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
    </script>

    @yield('scripts')
</body>

</html>
