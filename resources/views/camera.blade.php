@extends('layouts.app')

@section('title', 'Camera - Photobox by Binar')

@section('styles')
    <style>
        .camera-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .video-wrapper {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 0;

            width: 100%;
            aspect-ratio: 3 / 2;
            /* Sesuaikan dengan targetRatio di JS */
            margin: 0 auto;
        }

        #videoElement {
            width: 100%;
            height: 100%;
            display: block;
            border-radius: 18px;
            object-fit: cover;
            transform: scaleX(-1);
            background: #000;
        }

        /* Container Overlay (Posisi Indikator) */
        .video-overlay {
            position: absolute;
            top: 15px;
            /* Sedikit lebih rapat ke atas di mobile */
            left: 15px;
            right: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            pointer-events: none;
            z-index: 10;
        }

        /* Indikator LIVE */
        .live-indicator {
            background: rgba(239, 68, 68, 0.9);
            color: white;
            padding: 0.4rem 0.8rem;
            /* Padding lebih slim */
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.75rem;
            /* Font default lebih kecil */
            display: flex;
            align-items: center;
            gap: 0.4rem;
            animation: pulse 2s infinite;
            backdrop-filter: blur(8px);
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: blink 1s infinite;
        }

        /* Timer Display Responsif */
        .timer-display {
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 15px;
            font-size: 1.4rem;
            /* Lebih kecil agar tidak menutupi layar */
            font-weight: 800;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* --- Responsive Breakpoints --- */

        /* Tablet (iPad) */
        @media (min-width: 768px) {
            .live-indicator {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }

            .live-dot {
                width: 10px;
                height: 10px;
            }

            .timer-display {
                font-size: 2rem;
                padding: 0.75rem 1.5rem;
            }

            .video-overlay {
                top: 25px;
                left: 25px;
                right: 25px;
            }
        }

        /* Mobile sangat kecil (iPhone SE / Fold) */
        @media (max-width: 380px) {
            .live-indicator {
                padding: 0.3rem 0.6rem;
                font-size: 0.65rem;
            }

            .timer-display {
                font-size: 1.2rem;
                padding: 0.4rem 1rem;
            }
        }

        #canvas {
            display: none;
        }

        .thumbnail-container {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            padding: 1rem 0;
            scroll-behavior: smooth;
        }

        .thumbnail-card {
            position: relative;
            min-width: 120px;
            height: 160px;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: 4px solid transparent;
        }

        .thumbnail-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .thumbnail-card.selected {
            border-color: #667eea;
            box-shadow: 0 0 0 2px #667eea;
        }

        .thumbnail-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumbnail-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 50%, rgba(0, 0, 0, 0.5) 100%);
        }

        .thumbnail-number {
            position: absolute;
            bottom: 8px;
            left: 8px;
            background: white;
            color: #2d3748;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .thumbnail-delete {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(239, 68, 68, 0.95);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            z-index: 2;
            transition: opacity 0.3s ease;
            border: none;
            cursor: pointer;
        }

        /* .thumbnail-card:hover .thumbnail-delete {
                    opacity: 1;
                } */

        .capture-zone {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .progress-indicator {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .progress-dot {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            color: #a0aec0;
            position: relative;
            transition: all 0.3s ease;
        }

        .progress-dot.filled {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .progress-dot.active {
            animation: pulse 1.5s infinite;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .flash-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.1s;
            z-index: 9999;
        }

        .flash-overlay.active {
            opacity: 1;
        }

        .thumbnail-preview {
            position: absolute;
            bottom: 8px;
            /* Di bawah */
            right: 8px;
            /* Di kanan */
            background: rgba(102, 126, 234, 0.95);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            z-index: 5;
            /* Pastikan di atas overlay gambar */
            transition: transform 0.2s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            /* opacity: 1 secara default (hapus opacity: 0) */
        }

        /* Efek sedikit membesar saat diklik/hover agar lebih interaktif */
        .thumbnail-preview:hover {
            transform: scale(1.1);
            background: rgba(102, 126, 234, 1);
        }

        /* Container Tombol */
        .action-buttons-container {
            display: flex;
            flex-direction: column;
            /* Default: Tumpuk ke bawah (Mobile) */
            gap: 10px;
            width: 100%;
            margin-top: 1.5rem;
        }

        /* Base style untuk semua tombol aksi */
        .action-buttons-container .btn {
            border-radius: 15px;
            padding: 12px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            width: 100%;
            /* Full width di mobile */
        }

        /* Khusus Upload/Save Button dengan warna gradient agar konsisten */
        #saveBtn {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            border: none;
            box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
        }

        /* Media Query: Tablet & Desktop */
        @media (min-width: 576px) {
            .action-buttons-container {
                flex-direction: row;
                /* Berdampingan di layar lebar */
                justify-content: center;
            }

            .action-buttons-container .btn {
                width: auto;
                /* Ukuran mengikuti konten di desktop */
                min-width: 180px;
            }
        }

        /* Sentuhan interaktif saat ditekan */
        .action-buttons-container .btn:active {
            transform: scale(0.95);
        }
    </style>
@endsection

@section('content')
    <div class="container camera-container animate__animated animate__fadeIn">
        <!-- Header -->
        <div class="text-center">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo"
                 style="max-height: 120px; width: auto; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));">

            <h2 class="fw-bold text-white mb-2"> <i class="bi bi-camera-video-fill"></i> Capture Your Moments</h2>

            <p class="text-white-50 fst-italic" style="max-width: 500px; margin-left: auto; margin-right: auto;">
                Ready, set, pose! Let's make some memories.
            </p>
        </div>

        <!-- Video Preview -->
        <div class="capture-zone mb-2 mt-2 animate__animated animate__fadeInUp">
            <div class="video-wrapper">
                <div class="video-overlay">
                    <div class="live-indicator">
                        <span class="live-dot"></span>
                        LIVE
                    </div>

                    <div class="timer-display" id="timerDisplay" style="display: none;">3</div>
                </div>
                <video id="videoElement" autoplay playsinline></video>
                <canvas id="canvas"></canvas>
            </div>

            <!-- Capture Button -->
            <div class="text-center mt-4">
                <button id="captureBtn" class="btn btn-primary btn-lg px-5 py-3">
                    <i class="bi bi-camera-fill me-2"></i>
                    <span id="captureText">Take Photo</span>
                </button>
            </div>
        </div>

        <!-- Thumbnails -->
        <div id="thumbnailContainer" class="thumbnail-container" style="display: none;"></div>

        <!-- Actions -->
        <div class="action-buttons-container">
            <button id="retakeBtn" class="btn btn-warning btn-lg" style="display: none;">
                <i class="bi bi-arrow-counterclockwise"></i> Start Over
            </button>

            <button id="saveBtn" class="btn btn-success btn-lg" style="display: none;">
                <i class="bi bi-upload"></i> Upload Photos
            </button>
        </div>
    </div>

    <!-- Flash overlay -->
    <div class="flash-overlay" id="flashOverlay"></div>

@endsection

@section('scripts')
    <script>
        const sessionId = '{{ $sessionId }}';
        const requiredPhotos = {{ $requiredPhotos }};
        let photoCount = 0;
        let stream = null;
        let photos = [];

        const video = document.getElementById('videoElement');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const captureBtn = document.getElementById('captureBtn');
        const captureText = document.getElementById('captureText');
        const retakeBtn = document.getElementById('retakeBtn');
        const saveBtn = document.getElementById('saveBtn');
        // const nextBtn = document.getElementById('nextBtn');
        const thumbnailContainer = document.getElementById('thumbnailContainer');
        const timerDisplay = document.getElementById('timerDisplay');
        const flashOverlay = document.getElementById('flashOverlay');

        // Initialize camera
        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: {
                            ideal: 1920
                        },
                        height: {
                            ideal: 1080
                        },
                        facingMode: 'user'
                    }
                });
                video.srcObject = stream;
            } catch (error) {
                console.error('Error accessing camera:', error);
                alert('Unable to access camera. Please check permissions.');
            }
        }

        // Countdown timer
        async function startCountdown() {
            let count = 3;
            timerDisplay.style.display = 'block';
            timerDisplay.textContent = count;
            captureBtn.disabled = true;

            for (let i = count; i > 0; i--) {
                timerDisplay.textContent = i;
                await new Promise(resolve => setTimeout(resolve, 1000));
            }

            timerDisplay.style.display = 'none';
            await capturePhoto();
        }

        // Capture photo
        async function capturePhoto() {
            // 1. Efek Flash
            flashOverlay.classList.add('active');
            setTimeout(() => flashOverlay.classList.remove('active'), 100);

            // 2. Setup Canvas (Crop 3:2)
            const targetRatio = 3 / 2;
            let canvasHeight = video.videoHeight;
            let canvasWidth = video.videoHeight * targetRatio;

            if (canvasWidth > video.videoWidth) {
                canvasWidth = video.videoWidth;
                canvasHeight = video.videoWidth / targetRatio;
            }

            canvas.width = canvasWidth;
            canvas.height = canvasHeight;

            const sx = (video.videoWidth - canvasWidth) / 2;
            const sy = (video.videoHeight - canvasHeight) / 2;

            ctx.save();
            ctx.scale(-1, 1);
            ctx.translate(-canvas.width, 0);
            ctx.drawImage(video, sx, sy, canvasWidth, canvasHeight, 0, 0, canvas.width, canvas.height);
            ctx.restore();

            const imageData = canvas.toDataURL('image/jpeg', 0.9);

            // 3. Auto-Save ke Database
            try {
                captureBtn.disabled = true;
                const currentSessionId = window.sessionId || localStorage.getItem('photo_session_id');

                const response = await axios.post('/api/photos/upload', {
                    session_id: currentSessionId,
                    images: [imageData] // Kirim satu foto
                });

                if (response.data.success) {
                    // PUSH KE ARRAY LOKAL AGAR MUNCUL DI UI
                    photos.push(imageData);
                    photoCount++;

                    // Panggil Update UI agar thumbnail muncul
                    updateUI();

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Photo saved successfully!'
                    });
                }
            } catch (error) {
                console.error('Save error:', error);
                Swal.fire('Error', 'Gagal menyimpan foto ke server.', 'error');
            } finally {
                captureBtn.disabled = (photoCount >= requiredPhotos);
            }
        }

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

        // Update UI
        function updateUI() {
            // 1. Update Progress Dots
            for (let i = 1; i <= requiredPhotos; i++) {
                const dot = document.getElementById(`dot${i}`);
                if (dot) {
                    dot.classList.remove('filled', 'active');
                    if (i <= photoCount) {
                        dot.classList.add('filled');
                        dot.innerHTML = '<i class="bi bi-check-lg"></i>';
                    } else if (i === photoCount + 1) {
                        dot.classList.add('active');
                        dot.textContent = i;
                    } else {
                        dot.textContent = i;
                    }
                }
            }

            // 2. Render Thumbnails dengan Tombol Preview & Delete
            if (photoCount > 0) {
                thumbnailContainer.style.display = 'flex';
                thumbnailContainer.innerHTML = photos.map((photo, index) => `
                    <div class="thumbnail-card animate__animated animate__zoomIn">
                        <img src="${photo}" alt="Photo ${index + 1}">
                        <div class="thumbnail-number">${index + 1}</div>

                        <button class="thumbnail-preview btn-preview-action" onclick="previewImage('${photo}')">
                            <i class="bi bi-eye-fill"></i>
                        </button>

                        <button class="thumbnail-delete" onclick="deletePhoto(${index})">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>
                `).join('');

                retakeBtn.style.display = 'inline-block';

                // Munculkan tombol Finish jika sudah mencapai kuota
                // if (photoCount >= requiredPhotos) {
                saveBtn.style.display = 'inline-block';
                saveBtn.innerHTML = '<i class="bi bi-check-all"></i> Finish & Preview';
                // }
            } else {
                thumbnailContainer.style.display = 'none';
                retakeBtn.style.display = 'none';
                saveBtn.style.display = 'none';
            }

            // 3. Update Status Tombol Capture
            if (photoCount >= requiredPhotos) {
                captureBtn.disabled = true;
                captureText.textContent = 'All Photos Captured!';
            } else {
                captureBtn.disabled = false;
                captureText.textContent = 'Take Photo';
            }
        }

        window.deletePhoto = async (index) => {
            const result = await Swal.fire({
                title: 'Delete this photo?',
                text: "You'll need to retake this shot.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                reverseButtons: true
            });

            if (result.isConfirmed) {
                try {
                    // 1. Tampilkan loading agar user tidak klik berkali-kali
                    Swal.fire({
                        title: 'Deleting...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const currentSessionId = window.sessionId || localStorage.getItem('photo_session_id');

                    // 2. Kirim request ke Backend
                    const response = await axios.post('/api/photos/delete', {
                        session_id: currentSessionId,
                        index: index // Mengirim index 0, 1, 2...
                    });

                    if (response.data.success) {
                        // 3. Update State Lokal hanya jika backend sukses
                        photos.splice(index, 1);
                        photoCount--;

                        // 4. Render ulang UI
                        updateUI();

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Photo deleted',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed!',
                        text: error.response?.data?.message || 'Could not delete photo from server.'
                    });
                }
            }
        };

        // Retake all
        retakeBtn.addEventListener('click', () => {
            if (photos.length === 0) return; // Tidak ada foto, tidak perlu reset

            Swal.fire({
                title: 'Retake All Photos?',
                text: "This will delete all current photos but stay in the same session.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Retake All',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                    try {
                        const currentSessionId = window.sessionId || localStorage.getItem(
                            'photo_session_id');
                        const response = await axios.post('/api/photos/clear-session', {
                            session_id: currentSessionId
                        });
                        return response.data;
                    } catch (error) {
                        Swal.showValidationMessage(
                            `Error: ${error.response?.data?.message || 'Server error'}`);
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    // 1. Reset variabel lokal di JavaScript
                    photoCount = 0;
                    photos = [];

                    // 2. Refresh UI (Dots kembali ke angka, thumbnail hilang, tombol capture aktif lagi)
                    updateUI();

                    // 3. Notifikasi sukses
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Ready to retake!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        });

        // Capture button click
        captureBtn.addEventListener('click', () => {
            if (photoCount < requiredPhotos) {
                startCountdown();
            }
        });

        // saveBtn.addEventListener('click', async () => {
        //     if (photos.length === 0) {
        //         Swal.fire('Opps!', 'Ambil foto terlebih dahulu.', 'warning');
        //         return;
        //     }

        //     try {
        //         // Loading state
        //         saveBtn.disabled = true;
        //         saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

        //         const response = await axios.post('/api/photos/upload', {
        //             session_id: sessionId,
        //             images: photos, // Mengirimkan seluruh array foto
        //         });

        //         if (response.data.success) {
        //             Swal.fire({
        //                 title: 'Berhasil!',
        //                 text: 'Semua foto telah tersimpan ke database.',
        //                 icon: 'success',
        //                 confirmButtonText: 'Lanjut'
        //             }).then(() => {
        //                 // Sembunyikan tombol upload, tampilkan tombol generate
        //                 saveBtn.style.display = 'none';
        //                 // if (photoCount >= requiredPhotos) {
        //                 //     nextBtn.style.display = 'inline-block';
        //                 // }
        //             });
        //         }
        //     } catch (error) {
        //         console.error('Error saving photos:', error);
        //         Swal.fire('Gagal!', 'Terjadi kesalahan saat mengunggah foto.', 'error');
        //     } finally {
        //         saveBtn.disabled = false;
        //         saveBtn.innerHTML = '<i class="bi bi-magic"></i> Upload Photos';
        //     }
        // });
        saveBtn.addEventListener('click', () => {
            Swal.fire({
                title: 'Finish Session?',
                text: "This will complete your session and reset the booth for the next user.",
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, I\'m Done!',
                cancelButtonText: 'Go Back',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // 1. Tampilkan pesan terima kasih sebentar
                    Swal.fire({
                        title: 'Thank You!',
                        text: 'Your session has ended.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });

                    // 2. Hapus Session ID dari LocalStorage agar sistem reset
                    localStorage.removeItem('photo_session_id');

                    // 3. Reset variabel lokal (opsional jika langsung redirect)
                    photos = [];
                    photoCount = 0;

                    // 4. Redirect ke halaman awal (landing page/start screen)
                    // Ganti '/' dengan route awal aplikasi Anda
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 2000);
                }
            });
        });

        // Next button (Go to editor to customize)
        // nextBtn.addEventListener('click', async () => {
        //     // User can choose to:
        //     // 1. Auto-generate and skip editor
        //     // 2. Go to editor to customize

        //     const choice = confirm(
        //         'Do you want to customize your strip?\n\n' +
        //         'Click OK to open editor and add stickers/text.\n' +
        //         'Click Cancel to auto-generate and skip editor.'
        //     );

        //     if (choice) {
        //         // Go to editor for customization
        //         window.location.href = '/editor?sessionId=' + sessionId;
        //     } else {
        //         // Auto-generate strip
        //         try {
        //             nextBtn.disabled = true;
        //             nextBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generating...';

        //             const response = await axios.post('/api/strip/generate', {
        //                 session_id: sessionId
        //             });

        //             if (response.data.success) {
        //                 alert('Strip generated successfully! 🎉');
        //                 window.location.href = '/preview?session=' + sessionId;
        //             } else {
        //                 alert('Failed to generate strip. Please try again.');
        //                 nextBtn.disabled = false;
        //                 nextBtn.innerHTML = '<i class="bi bi-magic"></i> Generate Strip';
        //             }
        //         } catch (error) {
        //             console.error('Error generating strip:', error);
        //             alert('Failed to generate strip. Please try again.');
        //             nextBtn.disabled = false;
        //             nextBtn.innerHTML = '<i class="bi bi-magic"></i> Generate Strip';
        //         }
        //     }
        // });

        // Start camera on load
        // startCamera();

        const sessionModalElement = document.getElementById('sessionModal');
        const sessionModal = new bootstrap.Modal(sessionModalElement);
        const startSessionForm = document.getElementById('startSessionForm');

        // Tampilkan modal saat halaman siap
        document.addEventListener('DOMContentLoaded', () => {
            sessionModal.show();
        });

        // Tangani submit form
        startSessionForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const nameInput = document.getElementById('customerName');
            const nameError = document.getElementById('nameError'); // Pastikan ID ini sesuai di HTML
            const name = nameInput.value.trim();
            const pkg = document.querySelector('input[name="package"]:checked').value;
            const submitBtn = this.querySelector('button[type="submit"]');

            // 1. Validasi Sederhana di Frontend
            if (!name) {
                nameInput.classList.add('is-invalid');
                if (nameError) nameError.classList.remove('d-none');
                nameInput.focus();
                return;
            }

            // Reset state jika validasi berhasil
            nameInput.classList.remove('is-invalid');
            if (nameError) nameError.classList.add('d-none');

            try {
                // 2. Loading State (Gunakan tema coklat di spinner jika perlu)
                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm" role="status"></span> Setting up...';

                // 3. Kirim data ke backend
                const response = await axios.post('/api/init-session', {
                    customer_name: name,
                    package: pkg
                });

                if (response.data.success) {
                    // Update session ID global
                    window.sessionId = response.data.session_id;

                    // Sembunyikan modal dan jalankan kamera
                    const sessionModalObj = bootstrap.Modal.getInstance(document.getElementById(
                        'sessionModal'));
                    if (sessionModalObj) sessionModalObj.hide();

                    startCamera();

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `Welcome, ${name}!`,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        // Background tipis senada coklat
                        background: '#fdfaf7',
                        iconColor: '#a88568'
                    });
                }
            } catch (error) {
                console.error(error);

                // 4. Pesan Error Simpel & General
                Swal.fire({
                    title: 'Wait a moment...',
                    text: 'Something went wrong. Let’s give it another shot!',
                    icon: 'error',
                    confirmButtonText: 'Try Again',
                    confirmButtonColor: '#57351a', // Coklat gelap sesuai tema
                });

                // 5. Reset tombol di modal agar bisa diklik lagi
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-camera-fill me-2"></i>Open Camera';
            }
        });

        // Stop camera when leaving page
        window.addEventListener('beforeunload', () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
@endsection
