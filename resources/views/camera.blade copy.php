@extends('layouts.app')

@section('title', 'Camera - Photobooth')

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
        padding: 4px;
    }

    #videoElement {
        width: 100%;
        display: block;
        border-radius: 18px;
        transform: scaleX(-1);
        background: #000;
        min-height: 480px;
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
</style>
@endsection

@section('content')
<div class="container camera-container animate__animated animate__fadeIn">
    <!-- Header -->
    <div class="text-center mb-4">
        <h1 class="display-6 fw-bold text-white mb-2">
            <i class="bi bi-camera-video-fill"></i> Capture Your Moments
        </h1>
        <p class="text-white-50">Take {{ $requiredPhotos }} amazing photos to create your strip</p>
    </div>

    <!-- Video Preview -->
    <div class="capture-zone mb-4 animate__animated animate__fadeInUp">
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
        <div class="progress-indicator mt-4" id="progressIndicator">
            @for($i = 1; $i <= $requiredPhotos; $i++)
                <div class="progress-dot" id="dot{{ $i }}">{{ $i }}</div>
            @endfor
        </div>

        <!-- Capture Button -->
        <div class="text-center">
            <button id="captureBtn" class="btn btn-primary btn-lg px-5 py-3">
                <i class="bi bi-camera-fill me-2"></i>
                <span id="captureText">Take Photo</span>
            </button>
        </div>
    </div>

    <!-- Thumbnails -->
    <div id="thumbnailContainer" class="thumbnail-container" style="display: none;"></div>

    <!-- Actions -->
    <div class="text-center mt-4">
        <button id="retakeBtn" class="btn btn-warning btn-lg me-2" style="display: none;">
            <i class="bi bi-arrow-counterclockwise"></i> Start Over
        </button>
        <button id="saveBtn" class="btn btn-success btn-lg" style="display: none;">
            <i class="bi bi-magic"></i> Upload Foto
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
    const nextBtn = document.getElementById('nextBtn');
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
        // Flash effect
        flashOverlay.classList.add('active');
        setTimeout(() => flashOverlay.classList.remove('active'), 100);

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        // Draw video frame (mirrored)
        ctx.save();
        ctx.scale(-1, 1);
        ctx.drawImage(video, -canvas.width, 0, canvas.width, canvas.height);
        ctx.restore();

        const imageData = canvas.toDataURL('image/jpeg', 0.95);

        // Save photo
        try {
            const response = await axios.post('/api/photos/upload', {
                session_id: sessionId,
                image: imageData,
                metadata: {
                    photo_number: photoCount + 1,
                    timestamp: new Date().toISOString()
                }
            });

            if (response.data.success) {
                photos.push(imageData);
                photoCount++;
                updateUI();
            } else {
                console.error('Server responded with error:', response.data);
                alert('Failed to save photo: ' + (response.data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error saving photo:', error);
            if (error.response) {
                // Server responded with error status
                console.error('Response data:', error.response.data);
                console.error('Response status:', error.response.status);
                console.error('Validation errors:', error.response.data.errors);

                let errorMsg = 'Failed to save photo: ';
                if (error.response.data.errors) {
                    errorMsg += JSON.stringify(error.response.data.errors);
                } else {
                    errorMsg += (error.response.data.message || error.response.data.error || 'Server error');
                }
                alert(errorMsg);
            } else if (error.request) {
                // Request made but no response
                console.error('No response received:', error.request);
                alert('Failed to save photo: No response from server. Please check your connection.');
            } else {
                // Error in request setup
                console.error('Request error:', error.message);
                alert('Failed to save photo: ' + error.message);
            }
        }

        captureBtn.disabled = false;
    }

    // Delete photo
    window.deletePhoto = (index) => {
        photos.splice(index, 1);
        photoCount--;
        updateUI();
    };

    // Update UI
    function updateUI() {
        // Update progress dots
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

        // Update thumbnails
        if (photoCount > 0) {
            thumbnailContainer.style.display = 'flex';
            thumbnailContainer.innerHTML = photos.map((photo, index) => `
                <div class="thumbnail-card animate__animated animate__zoomIn">
                    <img src="${photo}" alt="Photo ${index + 1}">
                    <div class="thumbnail-number">${index + 1}</div>
                    <button class="thumbnail-delete" onclick="deletePhoto(${index})">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            `).join('');
            retakeBtn.style.display = 'inline-block';
        } else {
            thumbnailContainer.style.display = 'none';
            retakeBtn.style.display = 'none';
        }

        // Update buttons
        captureBtn.disabled = photoCount >= requiredPhotos;

        if (photoCount >= requiredPhotos) {
            captureText.textContent = 'All Photos Captured!';
            nextBtn.style.display = 'inline-block';
        } else {
            captureText.textContent = `Take Photo ${photoCount + 1}/${requiredPhotos}`;
            nextBtn.style.display = 'none';
        }
    }

    // Retake all
    retakeBtn.addEventListener('click', () => {
        Swal.fire({
            title: 'Start over?',
            text: "This will delete all captured photos.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, reset',
        }).then((result) => {
            if (result.isConfirmed) {
                photoCount = 0;
                photos = [];
                updateUI();
                Swal.fire('Reset!', 'All photos cleared.', 'success');
            }
        });
    });

    // Capture button click
    captureBtn.addEventListener('click', () => {
        if (photoCount < requiredPhotos) {
            startCountdown();
        }
    });

    // Next button (Go to editor to customize)
    nextBtn.addEventListener('click', async () => {
        // User can choose to:
        // 1. Auto-generate and skip editor
        // 2. Go to editor to customize

        const choice = confirm(
            'Do you want to customize your strip?\n\n' +
            'Click OK to open editor and add stickers/text.\n' +
            'Click Cancel to auto-generate and skip editor.'
        );

        if (choice) {
            // Go to editor for customization
            window.location.href = '/editor?sessionId=' + sessionId;
        } else {
            // Auto-generate strip
            try {
                nextBtn.disabled = true;
                nextBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generating...';

                const response = await axios.post('/api/strip/generate', {
                    session_id: sessionId
                });

                if (response.data.success) {
                    alert('Strip generated successfully! 🎉');
                    window.location.href = '/preview?session=' + sessionId;
                } else {
                    alert('Failed to generate strip. Please try again.');
                    nextBtn.disabled = false;
                    nextBtn.innerHTML = '<i class="bi bi-magic"></i> Generate Strip';
                }
            } catch (error) {
                console.error('Error generating strip:', error);
                alert('Failed to generate strip. Please try again.');
                nextBtn.disabled = false;
                nextBtn.innerHTML = '<i class="bi bi-magic"></i> Generate Strip';
            }
        }
    });

    // Start camera on load
    startCamera();

    // Stop camera when leaving page
    window.addEventListener('beforeunload', () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });
</script>
@endsection
