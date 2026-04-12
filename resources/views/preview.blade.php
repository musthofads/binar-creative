@extends('layouts.app')

@section('title', 'Preview - Photobooth')

@section('styles')
<style>
    .preview-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .qr-section {
        background: white;
        border-radius: 24px;
        padding: 3rem;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .qr-wrapper {
        display: inline-block;
        padding: 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        margin-bottom: 2rem;
    }

    .qr-code-box {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        display: inline-block;
    }

    #qrCodeImage {
        width: 280px;
        height: 280px;
        display: block;
    }

    .session-badge {
        display: inline-block;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        color: #667eea;
        padding: 0.75rem 2rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        border: 2px solid #667eea;
    }

    .gallery-url {
        background: #f7fafc;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        font-family: 'Courier New', monospace;
        font-size: 0.95rem;
        color: #2d3748;
        margin: 1.5rem 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .copy-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .copy-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin: 2rem 0;
    }

    .action-btn {
        padding: 1rem 2.5rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1.1rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-download {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: white;
    }

    .btn-download:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(72, 187, 120, 0.4);
    }

    .btn-open {
        background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
        color: white;
    }

    .btn-open:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(66, 153, 225, 0.4);
    }

    .how-to-use {
        background: #f7fafc;
        border-radius: 16px;
        padding: 2rem;
        margin-top: 2rem;
        text-align: left;
    }

    .how-to-use h5 {
        color: #2d3748;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .how-to-use ol {
        margin: 0;
        padding-left: 1.5rem;
    }

    .how-to-use li {
        color: #4a5568;
        margin-bottom: 0.5rem;
        line-height: 1.6;
    }

    .email-section {
        background: white;
        border-radius: 24px;
        padding: 3rem;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
        margin-top: 2rem;
    }

    .email-section h3 {
        color: #2d3748;
        font-weight: 700;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .email-form {
        max-width: 600px;
        margin: 0 auto;
    }

    .email-input-group {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .email-input-group input {
        flex: 1;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .email-input-group input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .email-input-group button {
        padding: 1rem 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .email-input-group button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    .photo-strip-preview {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        margin-top: 2rem;
    }

    .strip-image {
        width: 100%;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    }
</style>
@endsection

@section('content')
<div class="container preview-container animate__animated animate__fadeIn">
    <!-- Header -->
    <div class="text-center mb-4">
        <h1 class="display-5 fw-bold text-white mb-2">
            <i class="bi bi-qr-code-scan"></i> Your Photo Strip is Ready!
        </h1>
        <p class="text-white-50">Scan the QR code or download your photos</p>
    </div>

    <!-- QR Code Section -->
    <div class="qr-section animate__animated animate__fadeInUp">
        <div class="session-badge">
            <i class="bi bi-fingerprint"></i> Session: <span id="sessionIdDisplay">{{ $sessionId ?? 'LOADING' }}</span>
        </div>

        <div class="qr-wrapper">
            <div class="qr-code-box">
                <img id="qrCodeImage" src="" alt="QR Code" style="display: none;">
                <div id="qrLoader" class="spinner-border text-primary" role="status" style="width: 280px; height: 280px;">
                    <span class="visually-hidden">Loading QR...</span>
                </div>
            </div>
        </div>

        <div class="gallery-url">
            <span id="galleryUrlText">Loading...</span>
            <button class="copy-btn" onclick="copyToClipboard()">
                <i class="bi bi-clipboard"></i> Copy
            </button>
        </div>

        <div class="action-buttons">
            <button class="action-btn btn-download" onclick="downloadStrip()">
                <i class="bi bi-download"></i> Download
            </button>
            <button class="action-btn btn-open" onclick="openGallery()">
                <i class="bi bi-box-arrow-up-right"></i> Open Gallery
            </button>
        </div>

        <div class="how-to-use">
            <h5>
                <i class="bi bi-info-circle-fill text-primary"></i> How to Use
            </h5>
            <ol>
                <li>Scan the QR code with your phone camera</li>
                <li>Or click "Open Gallery" to view all photos</li>
                <li>Download your photos to save them</li>
                <li>Share the gallery link with friends!</li>
            </ol>
        </div>
    </div>

    <!-- Photo Strip Preview -->
    <div id="stripPreviewSection" class="photo-strip-preview animate__animated animate__fadeInUp" style="display: none;">
        <h3 class="text-center mb-4">
            <i class="bi bi-images"></i> Your Photo Strip
        </h3>
        <div class="text-center">
            <img id="stripImage" src="" alt="Photo Strip" class="strip-image">
        </div>
    </div>

    <!-- Email Section -->
    <div class="email-section animate__animated animate__fadeInUp">
        <h3>
            <i class="bi bi-envelope-heart-fill"></i> Send QR Code via Email
        </h3>
        <div class="email-form">
            <form id="emailForm" onsubmit="sendEmail(event)">
                <div class="email-input-group">
                    <input
                        type="email"
                        id="emailInput"
                        placeholder="Enter your email address"
                        required>
                    <button type="submit">
                        <i class="bi bi-send-fill"></i> Send
                    </button>
                </div>
            </form>
            <p class="text-center text-muted mt-2 mb-0">
                <small><i class="bi bi-shield-check"></i> We'll send you a QR code to access your photos anytime</small>
            </p>
        </div>
    </div>

    <!-- Back Button -->
    <div class="text-center mt-4">
        <a href="{{ route('camera') }}" class="btn btn-outline-light btn-lg">
            <i class="bi bi-camera-fill"></i> Take More Photos
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const sessionId = '{{ $sessionId ?? '' }}' || new URLSearchParams(window.location.search).get('session') || sessionStorage.getItem('photo_session_id');
    let galleryUrl = '';
    let stripImageUrl = '';

    // Initialize page
    async function init() {
        if (!sessionId) {
            alert('No session found. Please take photos first.');
            window.location.href = '{{ route('camera') }}';
            return;
        }

        document.getElementById('sessionIdDisplay').textContent = sessionId.substring(0, 8).toUpperCase();

        await loadSession();
        await loadStripPhoto();
    }

    // Load session and generate QR
    async function loadSession() {
        try {
            // Generate gallery URL
            galleryUrl = window.location.origin + '/gallery?session=' + sessionId;
            document.getElementById('galleryUrlText').textContent = galleryUrl;

            // Generate QR code (using API or generate locally)
            await generateQRCode(galleryUrl);
        } catch (error) {
            console.error('Error loading session:', error);
        }
    }

    // Generate QR Code
    async function generateQRCode(url) {
        try {
            // Using a QR code API service
            const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=${encodeURIComponent(url)}`;

            const qrImage = document.getElementById('qrCodeImage');
            qrImage.src = qrApiUrl;
            qrImage.style.display = 'block';

            document.getElementById('qrLoader').style.display = 'none';
        } catch (error) {
            console.error('Error generating QR code:', error);
            document.getElementById('qrLoader').innerHTML = '<p class="text-danger">Failed to generate QR code</p>';
        }
    }

    // Load strip photo
    async function loadStripPhoto() {
        try {
            const response = await axios.get('/api/photos', {
                params: { sessionId: sessionId }
            });

            if (response.data.success && response.data.photos && response.data.photos.length > 0) {
                const stripPhoto = response.data.photos.find(p => p.type === 'strip') || response.data.photos[0];
                stripImageUrl = stripPhoto.url;

                document.getElementById('stripImage').src = stripImageUrl;
                document.getElementById('stripPreviewSection').style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading strip photo:', error);
        }
    }

    // Copy to clipboard
    function copyToClipboard() {
        navigator.clipboard.writeText(galleryUrl).then(() => {
            const btn = event.target.closest('.copy-btn');
            const originalHtml = btn.innerHTML;

            btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
            btn.style.background = 'linear-gradient(135deg, #48bb78 0%, #38a169 100%)';

            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            }, 2000);
        }).catch(err => {
            alert('Failed to copy. Please copy manually: ' + galleryUrl);
        });
    }

    // Download strip
    async function downloadStrip() {
        try {
            if (!stripImageUrl) {
                alert('No photo strip available to download');
                return;
            }

            const link = document.createElement('a');
            link.href = stripImageUrl;
            link.download = `photobooth-strip-${sessionId.substring(0, 8)}.jpg`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } catch (error) {
            console.error('Error downloading strip:', error);
            alert('Failed to download. Please right-click the image and save it manually.');
        }
    }

    // Open gallery
    function openGallery() {
        window.open(galleryUrl, '_blank');
    }

    // Send email
    async function sendEmail(event) {
        event.preventDefault();

        const email = document.getElementById('emailInput').value;
        const btn = event.target.querySelector('button');
        const originalHtml = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';

        try {
            const response = await axios.post('/api/photos/send-email', {
                sessionId: sessionId,
                email: email,
                gallery_url: galleryUrl
            });

            if (response.data.success) {
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Sent!';
                btn.style.background = 'linear-gradient(135deg, #48bb78 0%, #38a169 100%)';

                setTimeout(() => {
                    document.getElementById('emailInput').value = '';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-send-fill"></i> Send';
                    btn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                }, 3000);

                // Show success message
                const form = document.getElementById('emailForm');
                const successMsg = document.createElement('div');
                successMsg.className = 'alert alert-success mt-3 animate__animated animate__fadeIn';
                successMsg.innerHTML = '<i class="bi bi-check-circle-fill"></i> Email sent successfully! Check your inbox.';
                form.appendChild(successMsg);

                setTimeout(() => successMsg.remove(), 5000);
            }
        } catch (error) {
            console.error('Error sending email:', error);
            alert('Failed to send email. Please try again.');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }

    // Initialize on page load
    init();
</script>
@endsection
