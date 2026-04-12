@extends('layouts.app')

@section('title', 'Photo Strip - Photobooth')

@section('styles')
<style>
    .strip-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .strip-showcase {
        background: white;
        border-radius: 24px;
        padding: 3rem;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .strip-frame {
        display: inline-block;
        padding: 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        margin-bottom: 2rem;
    }

    .strip-image-wrapper {
        background: white;
        padding: 1rem;
        border-radius: 16px;
        display: inline-block;
    }

    .strip-image {
        width: 100%;
        max-width: 800px;
        height: auto;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        display: block;
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        max-width: 800px;
        margin: 2rem auto;
    }

    .action-card {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .action-card:hover {
        transform: translateY(-5px);
        border-color: #667eea;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
    }

    .action-card i {
        font-size: 2.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.5rem;
    }

    .action-card h5 {
        color: #2d3748;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .action-card p {
        color: #718096;
        font-size: 0.85rem;
        margin: 0;
    }

    .stats-bar {
        display: flex;
        justify-content: center;
        gap: 2rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-label {
        color: #718096;
        font-size: 0.9rem;
    }
</style>
@endsection

@section('content')
<div class="container strip-container animate__animated animate__fadeIn">
    <!-- Header -->
    <div class="text-center mb-4">
        <h1 class="display-5 fw-bold text-white mb-2">
            <i class="bi bi-collection-fill"></i> Your Photo Strip
        </h1>
        <p class="text-white-50">Your memories in a perfect strip!</p>
    </div>

    <!-- Strip Showcase -->
    <div class="strip-showcase animate__animated animate__fadeInUp">
        <div id="stripContainer">
            <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="stats-bar" id="statsBar" style="display: none;">
            <div class="stat-item">
                <div class="stat-value" id="photoCount">4</div>
                <div class="stat-label">Photos</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="sessionDate">Today</div>
                <div class="stat-label">Created</div>
            </div>
        </div>
    </div>

    <!-- Action Grid -->
    <div class="action-grid mt-4">
        <div class="action-card" onclick="downloadStrip()">
            <i class="bi bi-download"></i>
            <h5>Download</h5>
            <p>Save to your device</p>
        </div>

        <div class="action-card" onclick="shareStrip()">
            <i class="bi bi-share-fill"></i>
            <h5>Share</h5>
            <p>Share with friends</p>
        </div>

        <div class="action-card" onclick="printStrip()">
            <i class="bi bi-printer-fill"></i>
            <h5>Print</h5>
            <p>Add to print queue</p>
        </div>

        <div class="action-card" onclick="window.location.href='{{ route('editor') }}'">
            <i class="bi bi-pencil-square"></i>
            <h5>Edit</h5>
            <p>Customize your strip</p>
        </div>
    </div>

    <!-- Navigation -->
    <div class="text-center mt-4">
        <a href="{{ route('camera') }}" class="btn btn-outline-light btn-lg me-2">
            <i class="bi bi-camera-fill"></i> New Session
        </a>
        <a href="{{ route('preview') }}" class="btn btn-light btn-lg">
            <i class="bi bi-qr-code-scan"></i> View QR Code
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const sessionId = '{{ $sessionId }}';
    let stripData = null;
    let stripUrl = '';

    // Initialize
    async function init() {
        await loadStrip();
    }

    // Load strip
    async function loadStrip() {
        try {
            const response = await axios.get('/api/photos', {
                params: { sessionId: sessionId }
            });

            if (response.data.success && response.data.photos && response.data.photos.length > 0) {
                stripData = response.data.photos.find(p => p.type === 'strip') || response.data.photos[0];
                stripUrl = stripData.url;
                displayStrip(stripData);
            } else {
                document.getElementById('stripContainer').innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-circle" style="font-size: 4rem; color: #cbd5e0;"></i>
                        <h4 class="mt-3 text-muted">No strip available yet</h4>
                        <p class="text-muted">Please take 4 photos first</p>
                        <a href="{{ route('camera') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-camera-fill"></i> Take Photos
                        </a>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading strip:', error);
            document.getElementById('stripContainer').innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-x-circle" style="font-size: 4rem; color: #ef4444;"></i>
                    <h4 class="mt-3 text-danger">Failed to load strip</h4>
                    <button onclick="init()" class="btn btn-outline-primary mt-3">
                        <i class="bi bi-arrow-clockwise"></i> Retry
                    </button>
                </div>
            `;
        }
    }

    // Display strip
    function displayStrip(strip) {
        document.getElementById('stripContainer').innerHTML = `
            <div class="strip-frame">
                <div class="strip-image-wrapper">
                    <img src="${strip.url}" alt="Photo Strip" class="strip-image">
                </div>
            </div>
        `;

        // Show stats
        document.getElementById('statsBar').style.display = 'flex';
        document.getElementById('sessionDate').textContent = new Date(strip.created_at || Date.now()).toLocaleDateString();
    }

    // Download strip
    function downloadStrip() {
        if (!stripUrl) {
            alert('No strip available to download');
            return;
        }

        const link = document.createElement('a');
        link.href = stripUrl;
        link.download = `photobooth-strip-${sessionId.substring(0, 8)}.jpg`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Share strip
    function shareStrip() {
        if (!stripUrl) {
            alert('No strip available to share');
            return;
        }

        if (navigator.share) {
            navigator.share({
                title: 'My Photobooth Strip',
                text: 'Check out my awesome photobooth strip!',
                url: stripUrl
            }).catch(err => console.error('Error sharing:', err));
        } else {
            // Fallback to copy link
            navigator.clipboard.writeText(stripUrl).then(() => {
                alert('Link copied to clipboard! Share it with your friends.');
            });
        }
    }

    // Print strip
    async function printStrip() {
        if (!stripData) {
            alert('No strip available to print');
            return;
        }

        try {
            const response = await axios.post('/api/print-queue', {
                photo_id: stripData.id,
                session_id: sessionId,
                type: 'strip'
            });

            if (response.data.success) {
                alert(`✅ Added to print queue! Queue number: ${response.data.queue_number || 'N/A'}`);
            }
        } catch (error) {
            console.error('Error adding to queue:', error);
            alert('Failed to add to print queue. Please try again.');
        }
    }

    // Initialize on load
    init();
</script>
@endsection
