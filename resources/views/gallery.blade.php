@extends('layouts.app')

@section('title', 'Gallery - Photobooth')

@section('styles')
<style>
    .gallery-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .layout-selector {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
        justify-content: center;
    }

    .layout-card {
        flex: 1;
        min-width: 180px;
        max-width: 220px;
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 3px solid transparent;
        position: relative;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .layout-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .layout-card.active {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    }

    .layout-card.active::after {
        content: '✓';
        position: absolute;
        top: 10px;
        right: 10px;
        width: 28px;
        height: 28px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }

    .layout-icon {
        width: 100px;
        height: 80px;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f7fafc;
        border-radius: 12px;
    }

    .layout-icon svg {
        width: 70px;
        height: 60px;
    }

    .layout-name {
        font-weight: 700;
        font-size: 1.1rem;
        color: #2d3748;
        margin-bottom: 0.25rem;
    }

    .layout-desc {
        font-size: 0.85rem;
        color: #718096;
        margin-bottom: 0.25rem;
    }

    .layout-photos {
        font-size: 0.75rem;
        color: #a0aec0;
    }

    .preview-container {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .photo-preview-strip {
        display: flex;
        gap: 15px;
        justify-content: center;
        align-items: center;
        padding: 2rem;
        background: #f7fafc;
        border-radius: 16px;
        min-height: 400px;
    }

    .photo-slot {
        width: 180px;
        height: 240px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        position: relative;
        border: 3px dashed #cbd5e0;
    }

    .photo-slot img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .photo-slot.empty {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #a0aec0;
        font-size: 3rem;
    }

    .btn-live-edit {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .preview-container:hover .btn-live-edit {
        opacity: 1;
    }

    .sticker-preview {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 1.5rem;
    }

    .sticker-badge {
        width: 50px;
        height: 50px;
        background: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .sticker-badge:hover {
        transform: scale(1.2);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .tab-pills {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }

    .tab-pill {
        padding: 0.5rem 1.5rem;
        border-radius: 20px;
        background: white;
        border: 2px solid #e2e8f0;
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .tab-pill:hover {
        border-color: #667eea;
        color: #667eea;
    }

    .tab-pill.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: transparent;
    }
</style>
@endsection

@section('content')
<div class="container gallery-container animate__animated animate__fadeIn">
    <!-- Header -->
    <div class="text-center mb-4">
        <h1 class="display-5 fw-bold text-white mb-2">
            <i class="bi bi-images"></i> Choose Your Layout
        </h1>
        <p class="text-white-50">Select a layout to preview and customize your photo strip</p>
    </div>

    <!-- Layout Options -->
    <div class="layout-selector">
        <div class="layout-card active" data-layout="classic">
            <div class="layout-icon">
                <svg viewBox="0 0 100 80" fill="none">
                    <rect x="10" y="10" width="80" height="15" rx="2" fill="#CBD5E0"/>
                    <rect x="10" y="30" width="80" height="15" rx="2" fill="#CBD5E0"/>
                    <rect x="10" y="50" width="80" height="15" rx="2" fill="#CBD5E0"/>
                </svg>
            </div>
            <div class="layout-name">Classic Strip</div>
            <div class="layout-desc">2" × 6"</div>
            <div class="layout-photos">4 photos</div>
        </div>

        <div class="layout-card" data-layout="triple">
            <div class="layout-icon">
                <svg viewBox="0 0 100 80" fill="none">
                    <rect x="10" y="5" width="80" height="20" rx="2" fill="#CBD5E0"/>
                    <rect x="10" y="30" width="80" height="20" rx="2" fill="#CBD5E0"/>
                    <rect x="10" y="55" width="80" height="20" rx="2" fill="#CBD5E0"/>
                </svg>
            </div>
            <div class="layout-name">Triple Strip</div>
            <div class="layout-desc">2" × 4.5"</div>
            <div class="layout-photos">3 photos</div>
        </div>

        <div class="layout-card" data-layout="grid">
            <div class="layout-icon">
                <svg viewBox="0 0 100 80" fill="none">
                    <rect x="10" y="10" width="35" height="30" rx="2" fill="#CBD5E0"/>
                    <rect x="55" y="10" width="35" height="30" rx="2" fill="#CBD5E0"/>
                    <rect x="10" y="45" width="35" height="30" rx="2" fill="#CBD5E0"/>
                    <rect x="55" y="45" width="35" height="30" rx="2" fill="#CBD5E0"/>
                </svg>
            </div>
            <div class="layout-name">2×3 Grid</div>
            <div class="layout-desc">4" × 6"</div>
            <div class="layout-photos">6 photos</div>
        </div>

        <div class="layout-card" data-layout="grid4">
            <div class="layout-icon">
                <svg viewBox="0 0 100 80" fill="none">
                    <rect x="15" y="10" width="30" height="30" rx="2" fill="#CBD5E0"/>
                    <rect x="55" y="10" width="30" height="30" rx="2" fill="#CBD5E0"/>
                    <rect x="15" y="45" width="30" height="30" rx="2" fill="#CBD5E0"/>
                    <rect x="55" y="45" width="30" height="30" rx="2" fill="#CBD5E0"/>
                </svg>
            </div>
            <div class="layout-name">Grid 4×4</div>
            <div class="layout-desc">4" × 4"</div>
            <div class="layout-photos">4 photos</div>
        </div>

        <div class="layout-card" data-layout="single">
            <div class="layout-icon">
                <svg viewBox="0 0 100 80" fill="none">
                    <rect x="25" y="15" width="50" height="50" rx="2" fill="#CBD5E0"/>
                </svg>
            </div>
            <div class="layout-name">Single Photo</div>
            <div class="layout-desc">4" × 6"</div>
            <div class="layout-photos">1 photo</div>
        </div>
    </div>

    <!-- Preview Area -->
    <div class="preview-container position-relative animate__animated animate__fadeInUp">
        <div id="photoPreview" class="photo-preview-strip">
            <!-- Photos will be loaded here -->
            <div class="photo-slot empty">
                <i class="bi bi-camera"></i>
            </div>
            <div class="photo-slot empty">
                <i class="bi bi-camera"></i>
            </div>
            <div class="photo-slot empty">
                <i class="bi bi-camera"></i>
            </div>
            <div class="photo-slot empty">
                <i class="bi bi-camera"></i>
            </div>
        </div>

        <button class="btn btn-live-edit">
            <i class="bi bi-pencil"></i> LIVE EDIT
        </button>

        <!-- Sticker Preview -->
        <div class="sticker-preview">
            <div class="text-center w-100 mb-2">
                <span class="badge bg-light text-dark" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                    ✨ <strong>Stickers:</strong> <span class="text-primary">NEW</span>
                </span>
            </div>
            <div class="tab-pills w-100 justify-content-center d-flex">
                <div class="tab-pill active">All</div>
                <div class="tab-pill">Accessories</div>
                <div class="tab-pill">Facial</div>
                <div class="tab-pill">Emotions</div>
                <div class="tab-pill">Shapes</div>
                <div class="tab-pill">Communication</div>
                <div class="tab-pill">Nature</div>
                <div class="tab-pill">Symbols</div>
                <div class="tab-pill">Fantasy</div>
            </div>
            <div class="d-flex justify-content-center gap-2 flex-wrap mt-2">
                <div class="sticker-badge">😎</div>
                <div class="sticker-badge">👓</div>
                <div class="sticker-badge">👑</div>
                <div class="sticker-badge">💖</div>
                <div class="sticker-badge">⭐</div>
                <div class="sticker-badge">🎩</div>
                <div class="sticker-badge">👀</div>
                <div class="sticker-badge">😊</div>
                <div class="sticker-badge">🌟</div>
                <div class="sticker-badge">✨</div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="text-center mt-4">
        <a href="{{ route('camera') }}" class="btn btn-primary btn-lg me-2">
            <i class="bi bi-camera-fill"></i> Take Photos
        </a>
        <a href="{{ route('editor') }}" class="btn btn-success btn-lg">
            <i class="bi bi-palette-fill"></i> Go to Editor
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Layout selector
    document.querySelectorAll('.layout-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.layout-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');

            const layout = this.getAttribute('data-layout');
            updatePreview(layout);
        });
    });

    // Update preview based on layout
    function updatePreview(layout) {
        const container = document.getElementById('photoPreview');
        let slots = 4;

        switch(layout) {
            case 'triple':
                slots = 3;
                break;
            case 'grid':
                slots = 6;
                break;
            case 'grid4':
                slots = 4;
                break;
            case 'single':
                slots = 1;
                break;
            default:
                slots = 4;
        }

        container.innerHTML = '';
        for(let i = 0; i < slots; i++) {
            container.innerHTML += `
                <div class="photo-slot empty">
                    <i class="bi bi-camera"></i>
                </div>
            `;
        }

        // Load actual photos if available
        loadPhotos();
    }

    // Load photos
    async function loadPhotos() {
        // Implementation here
    }

    // Live edit button
    document.querySelector('.btn-live-edit').addEventListener('click', () => {
        window.location.href = '{{ route('editor') }}';
    });
</script>
@endsection
