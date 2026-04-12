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
        background: linear-gradient(135deg, #a88568 0%, #57351a 100%);
        padding: 0;

        width: 100%;
        aspect-ratio: 3 / 2; /* Sesuaikan dengan targetRatio di JS */
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

    .video-overlay {
        position: absolute;
        top: 20px;
        left: 20px;
        right: 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        pointer-events: none;
        z-index: 10;
    }

    .live-indicator {
        background: rgba(239, 68, 68, 0.95);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        animation: pulse 2s infinite;
        backdrop-filter: blur(10px);
    }

    .live-dot {
        width: 10px;
        height: 10px;
        background: white;
        border-radius: 50%;
        animation: blink 1s infinite;
    }

    @keyframes blink {
        0%, 50% { opacity: 1; }
        51%, 100% { opacity: 0.3; }
    }

    .timer-display {
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 20px;
        font-size: 1.8rem;
        font-weight: 700;
        backdrop-filter: blur(10px);
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
        background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.5) 100%);
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
        opacity: 0;
        transition: opacity 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .thumbnail-card:hover .thumbnail-delete {
        opacity: 1;
    }

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
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
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
        bottom: 8px; /* Di bawah */
        right: 8px;  /* Di kanan */
        background: rgba(102, 126, 234, 0.95);
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        z-index: 5; /* Pastikan di atas overlay gambar */
        transition: transform 0.2s ease;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        /* opacity: 1 secara default (hapus opacity: 0) */
    }

    /* Efek sedikit membesar saat diklik/hover agar lebih interaktif */
    .thumbnail-preview:hover {
        transform: scale(1.1);
        background: rgba(102, 126, 234, 1);
    }
</style>
@endsection

@section('content')
    <div class="container camera-container animate__animated animate__fadeIn">
        <!-- Header -->
        <div class="text-center"> <img src="{{ asset('assets/images/logo.png') }}"
            alt="Logo"
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

        <!-- Progress Indicator -->
        {{-- <div class="progress-indicator mt-4" id="progressIndicator">
            @for($i = 1; $i <= $requiredPhotos; $i++)
                <div class="progress-dot" id="dot{{ $i }}">{{ $i }}</div>
            @endfor
        </div> --}}

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
    <div class="text-center mt-2">
        <button id="retakeBtn" class="btn btn-warning btn-lg me-2" style="display: none;">
            <i class="bi bi-arrow-counterclockwise"></i> Start Over
        </button>
        <button id="saveBtn" class="btn btn-success btn-lg" style="display: none;">
            <i class="bi bi-upload"></i> Upload Photos
        </button>
        <button id="nextBtn" class="btn btn-success btn-lg" style="display: none;">
            <i class="bi bi-magic"></i> Generate Strip
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
                    width: { ideal: 1920 },
                    height: { ideal: 1080 },
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
        let count = 1;
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
        // Flash effect
        flashOverlay.classList.add('active');
        setTimeout(() => flashOverlay.classList.remove('active'), 100);

        const targetRatio = 3 / 2;

        // Tentukan dimensi canvas berdasarkan video input
        // Kita ambil tinggi penuh video, lalu lebarnya disesuaikan ke 3:4
        let canvasHeight = video.videoHeight;
        let canvasWidth = video.videoHeight * targetRatio;

        // Jika lebar yang dihitung melebihi lebar video asli, balik logikanya
        if (canvasWidth > video.videoWidth) {
            canvasWidth = video.videoWidth;
            canvasHeight = video.videoWidth / targetRatio;
        }

        canvas.width = canvasWidth;
        canvas.height = canvasHeight;

        // Hitung posisi tengah (offset) agar hasil foto presisi di tengah
        const sx = (video.videoWidth - canvasWidth) / 2;
        const sy = (video.videoHeight - canvasHeight) / 2;

        ctx.save();

        // Mirroring
        ctx.scale(-1, 1);
        ctx.translate(-canvas.width, 0);

        // drawImage(image, sx, sy, sWidth, sHeight, dx, dy, dWidth, dHeight)
        ctx.drawImage(
            video,
            sx, sy, canvasWidth, canvasHeight, // Area sumber (crop tengah)
            0, 0, canvas.width, canvas.height   // Area tujuan
        );

        ctx.restore();

        const imageData = canvas.toDataURL('image/jpeg', 0.9);
        photos.push(imageData);
        photoCount++;

        updateUI();
        captureBtn.disabled = false;
    }

    // Delete photo
    window.deletePhoto = (index) => {
        photos.splice(index, 1);
        photoCount--;
        updateUI();
    };

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
        // 1. Update progress dots
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

        // 2. Update Thumbnails & Save/Retake Buttons
        if (photoCount > 0) {
            thumbnailContainer.style.display = 'flex';
            thumbnailContainer.innerHTML = photos.map((photo, index) => `
                <div class="thumbnail-card animate__animated animate__zoomIn">
                    <img src="${photo}" alt="Photo ${index + 1}">

                    <div class="thumbnail-number">${index + 1}</div>

                    <button class="thumbnail-preview btn-preview-action" data-index="${index}">
                        <i class="bi bi-eye-fill"></i>
                    </button>

                    <button class="thumbnail-delete" onclick="deletePhoto(${index})">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            `).join('');

            document.querySelectorAll('.btn-preview-action').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idx = this.getAttribute('data-index');
                    previewImage(photos[idx]); // Ambil langsung dari array photos
                });
            });

            retakeBtn.style.display = 'inline-block';
            saveBtn.style.display = 'inline-block'; // Tampilkan save jika ada foto
        } else {
            thumbnailContainer.style.display = 'none';
            retakeBtn.style.display = 'none';
            saveBtn.style.display = 'none';
        }

        // 3. Update Capture Button State (Logic Final)
        if (photoCount >= requiredPhotos) {
            captureBtn.disabled = true;
            captureText.textContent = 'All Photos Captured!';
            saveBtn.className = "btn btn-success btn-lg px-5";
        } else {
            captureBtn.disabled = false;
            captureText.textContent = `Take Photo`;
            // captureText.textContent = `Take Photo ${photoCount + 1}/${requiredPhotos}`;
            saveBtn.className = "btn btn-success btn-lg px-5"; // Reset class jika belum penuh
        }
    }

    // Retake all
    retakeBtn.addEventListener('click', () => {
        // Check if there are no photos to clear
        if (photos.length === 0) {
            Swal.fire({
                title: 'Empty Gallery',
                text: 'You haven\'t captured any photos yet.',
                icon: 'info',
                confirmButtonColor: '#3085d6',
            });
            return;
        }

        Swal.fire({
            title: 'Retake All Photos?',
            text: "This will permanently delete your current progress!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33', // Red for destructive action
            cancelButtonColor: '#6c757d', // Grey for cancel
            confirmButtonText: 'Yes, start over',
            cancelButtonText: 'No, keep them',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Reset data
                photoCount = 0;
                photos = [];

                // Update UI
                updateUI();

                // Toast notification for non-intrusive feedback
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Gallery cleared successfully'
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
    saveBtn.addEventListener('click', async () => {
        if (photos.length === 0) {
            Swal.fire({
                title: 'No Photos Yet',
                text: 'Capture some memories before uploading!',
                icon: 'warning',
                confirmButtonColor: '#a88568', // Brown theme
            });
            return;
        }

        const confirm = await Swal.fire({
            title: 'Ready to Save?',
            text: `You are about to save ${photos.length} beautiful photos.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#57351a', // Dark brown
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Save Them!',
            cancelButtonText: 'Not yet',
            reverseButtons: true
        });

        if (!confirm.isConfirmed) return;

        try {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

            // Pastikan sessionId diambil dari sumber yang benar (misal variabel global atau session storage)
            const currentSessionId = window.sessionId || localStorage.getItem('photo_session_id');

            const response = await axios.post('/api/photos/upload', {
                session_id: currentSessionId, // Ini yang dicek Laravel (exists:photobooth_sessions)
                images: photos,
            });

            if (response.data.success) {
                await Swal.fire({
                    title: 'All Set!',
                    text: 'Your photos have been saved successfully.',
                    icon: 'success',
                    confirmButtonColor: '#57351a',
                });
                // Reset halaman
                window.location.reload();
            }
        } catch (error) {
            console.error('Error saving photos:', error);

            // Cek jika error 422 untuk memberikan pesan spesifik
            let errorMessage = 'Something went wrong. Let’s give it another shot!';
            if (error.response && error.response.status === 422) {
                errorMessage = 'System busy or session expired. Please try again.';
                console.log('Validation details:', error.response.data.errors);
            }

            Swal.fire({
                title: 'Wait a moment...',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: 'Try Again',
                confirmButtonColor: '#57351a',
            });
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-upload"></i> Upload Photos';
        }
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

        const name = document.getElementById('customerName').value;
        const pkg = document.querySelector('input[name="package"]:checked').value;

        try {
            // Loading state pada tombol modal
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

            // Kirim data ke backend
            const response = await axios.post('/api/init-session', {
                customer_name: name,
                package: pkg
            });

            if (response.data.success) {
                // Update session ID global
                window.sessionId = response.data.session_id;

                // Sembunyikan modal dan jalankan kamera
                sessionModal.hide();
                startCamera();

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `Welcome, ${name}!`,
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({
                title: 'Something went wrong',
                text: 'We couldn’t start the camera. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#57351a',
            });

            // Reset tombol di modal agar bisa diklik lagi
            const submitBtn = startSessionForm.querySelector('button[type="submit"]');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Open Camera <i class="bi bi-arrow-right ms-2"></i>';
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
