@extends('layouts.app')

@section('title', 'Photo Editor - Photobooth')

<!-- Fabric.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>

@section('styles')
<style>
    .editor-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .canvas-wrapper {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        text-align: center;
    }

    .canvas-container {
        margin: 0 auto !important;
    }

    .sticker-panel {
        max-height: 600px;
        overflow-y: auto;
        background: white;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .sticker-item {
        width: 60px;
        height: 60px;
        margin: 5px;
        cursor: pointer;
        border: 2px solid transparent;
        border-radius: 8px;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .sticker-item:hover {
        border-color: #667eea;
        transform: scale(1.1);
        background: #f8f9fa;
    }

    .text-style-btn {
        width: 100%;
        margin: 5px 0;
    }

    .color-picker {
        width: 100%;
        height: 40px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .tool-section {
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .tool-section h6 {
        font-weight: 600;
        margin-bottom: 10px;
    }
</style>
@endsection

@section('content')
<div class="container editor-container">
    <div class="row">
        <!-- Main Canvas Area -->
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-4">
                        <i class="bi bi-palette"></i> Photo Strip Editor
                    </h3>

                    <div class="canvas-wrapper text-center">
                        <canvas id="canvas" width="1800" height="1200"></canvas>
                    </div>

                    <!-- Canvas Controls -->
                    <div class="mt-4">
                        <div class="btn-group w-100" role="group">
                            <button id="deleteBtn" class="btn btn-danger" disabled>
                                <i class="bi bi-trash"></i> Delete Selected
                            </button>
                            <button id="clearBtn" class="btn btn-warning">
                                <i class="bi bi-eraser"></i> Clear All
                            </button>
                            <button id="undoBtn" class="btn btn-secondary">
                                <i class="bi bi-arrow-counterclockwise"></i> Undo
                            </button>
                            <button id="saveBtn" class="btn btn-success">
                                <i class="bi bi-save"></i> Save Strip
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar - Tools -->
        <div class="col-lg-3">
            <!-- Add Text -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5><i class="bi bi-type"></i> Add Text</h5>
                    <input type="text" id="textInput" class="form-control mb-2" placeholder="Enter text">
                    <button id="addTextBtn" class="btn btn-primary w-100">Add Text</button>

                    <hr>

                    <label>Font Size:</label>
                    <input type="range" id="fontSizeSlider" class="form-range" min="12" max="120" value="40">
                    <span id="fontSizeValue">40px</span>

                    <label class="mt-2">Text Color:</label>
                    <input type="color" id="textColorPicker" class="color-picker" value="#000000">

                    <button id="boldBtn" class="btn btn-outline-dark text-style-btn">
                        <i class="bi bi-type-bold"></i> Bold
                    </button>
                    <button id="italicBtn" class="btn btn-outline-dark text-style-btn">
                        <i class="bi bi-type-italic"></i> Italic
                    </button>
                </div>
            </div>

            <!-- Stickers -->
            <div class="card">
                <div class="card-body sticker-panel">
                    <h5><i class="bi bi-emoji-smile"></i> Stickers</h5>
                    <div id="stickerContainer" class="d-flex flex-wrap justify-content-center">
                        <!-- Stickers will be loaded here -->
                        <div class="sticker-item" data-sticker="❤️" style="font-size: 50px; display: flex; align-items: center; justify-content: center;">❤️</div>
                        <div class="sticker-item" data-sticker="😍" style="font-size: 50px; display: flex; align-items: center; justify-content: center;">😍</div>
                        <div class="sticker-item" data-sticker="🎉" style="font-size: 50px; display: flex; align-items: center; justify-content: center;">🎉</div>
                        <div class="sticker-item" data-sticker="⭐" style="font-size: 50px; display: flex; align-items: center; justify-content: center;">⭐</div>
                        <div class="sticker-item" data-sticker="🌟" style="font-size: 50px; display: flex; align-items: center; justify-content: center;">🌟</div>
                        <div class="sticker-item" data-sticker="💖" style="font-size: 50px; display: flex; align-items: center; justify-content: center;">💖</div>
                        <div class="sticker-item" data-sticker="🎈" style="font-size: 50px; display: flex; align-items: center; justify-content: center;">🎈</div>
                        <div class="sticker-item" data-sticker="🎊" style="font-size: 50px; display: flex; align-items: center; justify-content: center;">🎊</div>
                        <div class="sticker-item" data-sticker="✨" style="font-size: 50px; display: flex; align-items: center; justify-content: center;">✨</div>
                        <div class="sticker-item" data-sticker="💝" style="font-size: 50px; display: flex; align-items: center; justify-content: center;">💝</div>
                        <div class="sticker-item" data-sticker="🌈" style="font-size: 50px; display: flex; align-items: center; justify-content: center;">🌈</div>
                        <div class="sticker-item" data-sticker="🦋" style="font-size: 50px; display: flex; align-items: center; justify-content: center;">🦋</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Fabric.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>

<script>
const sessionId = '{{ $sessionId }}';
let canvas;
let historyStack = [];

// Initialize Fabric.js canvas
document.addEventListener('DOMContentLoaded', async function() {
    canvas = new fabric.Canvas('canvas', {
        backgroundColor: '#ffffff'
    });

    // Load photos from session
    await loadPhotosToCanvas();

    // Setup event listeners
    setupEventListeners();

    // Save initial state
    saveState();
});

// Load photos and arrange in strip
async function loadPhotosToCanvas() {
    try {
        const response = await axios.get('/api/photos', {
            params: { sessionId: sessionId }
        });

        if (response.data.success && response.data.photos.length > 0) {
            const photos = response.data.photos.slice(0, 4); // Max 4 photos
            const photoWidth = 400;
            const photoHeight = 600;
            const spacing = 50;
            let currentX = spacing;

            for (const photo of photos) {
                await addPhotoToCanvas(photo.url, currentX, 300, photoWidth, photoHeight);
                currentX += photoWidth + spacing;
            }

            canvas.renderAll();
        } else {
            alert('No photos found in this session. Please take photos first.');
        }
    } catch (error) {
        console.error('Error loading photos:', error);
        alert('Failed to load photos');
    }
}

// Add photo to canvas
function addPhotoToCanvas(imageUrl, left, top, width, height) {
    return new Promise((resolve, reject) => {
        fabric.Image.fromURL(imageUrl, function(img) {
            img.scaleToWidth(width);
            img.scaleToHeight(height);
            img.set({
                left: left,
                top: top,
                selectable: true,
                hasControls: true,
            });
            canvas.add(img);
            resolve();
        }, { crossOrigin: 'anonymous' });
    });
}

// Setup event listeners
function setupEventListeners() {
    // Add text
    document.getElementById('addTextBtn').addEventListener('click', function() {
        const text = document.getElementById('textInput').value || 'Your Text';
        const fontSize = parseInt(document.getElementById('fontSizeSlider').value);
        const color = document.getElementById('textColorPicker').value;

        const textObj = new fabric.Text(text, {
            left: 100,
            top: 100,
            fontSize: fontSize,
            fill: color,
            fontFamily: 'Arial'
        });

        canvas.add(textObj);
        canvas.setActiveObject(textObj);
        saveState();
    });

    // Font size slider
    document.getElementById('fontSizeSlider').addEventListener('input', function(e) {
        document.getElementById('fontSizeValue').textContent = e.target.value + 'px';
        const activeObj = canvas.getActiveObject();
        if (activeObj && activeObj.type === 'text') {
            activeObj.set('fontSize', parseInt(e.target.value));
            canvas.renderAll();
            saveState();
        }
    });

    // Text color
    document.getElementById('textColorPicker').addEventListener('change', function(e) {
        const activeObj = canvas.getActiveObject();
        if (activeObj && activeObj.type === 'text') {
            activeObj.set('fill', e.target.value);
            canvas.renderAll();
            saveState();
        }
    });

    // Bold
    document.getElementById('boldBtn').addEventListener('click', function() {
        const activeObj = canvas.getActiveObject();
        if (activeObj && activeObj.type === 'text') {
            activeObj.set('fontWeight', activeObj.fontWeight === 'bold' ? 'normal' : 'bold');
            canvas.renderAll();
            saveState();
        }
    });

    // Italic
    document.getElementById('italicBtn').addEventListener('click', function() {
        const activeObj = canvas.getActiveObject();
        if (activeObj && activeObj.type === 'text') {
            activeObj.set('fontStyle', activeObj.fontStyle === 'italic' ? 'normal' : 'italic');
            canvas.renderAll();
            saveState();
        }
    });

    // Stickers
    document.querySelectorAll('.sticker-item').forEach(item => {
        item.addEventListener('click', function() {
            const emoji = this.getAttribute('data-sticker');
            const textObj = new fabric.Text(emoji, {
                left: 200,
                top: 200,
                fontSize: 80
            });
            canvas.add(textObj);
            saveState();
        });
    });

    // Delete selected
    document.getElementById('deleteBtn').addEventListener('click', function() {
        const activeObj = canvas.getActiveObject();
        if (activeObj) {
            canvas.remove(activeObj);
            saveState();
        }
    });

    // Clear all
    document.getElementById('clearBtn').addEventListener('click', function() {
        if (confirm('Clear all objects?')) {
            canvas.clear();
            canvas.backgroundColor = '#ffffff';
            saveState();
        }
    });

    // Undo
    document.getElementById('undoBtn').addEventListener('click', function() {
        if (historyStack.length > 1) {
            historyStack.pop();
            const previousState = historyStack[historyStack.length - 1];
            canvas.loadFromJSON(previousState, function() {
                canvas.renderAll();
            });
        }
    });

    // Save
    document.getElementById('saveBtn').addEventListener('click', saveStripToBackend);

    // Object selection
    canvas.on('selection:created', function() {
        document.getElementById('deleteBtn').disabled = false;
    });

    canvas.on('selection:cleared', function() {
        document.getElementById('deleteBtn').disabled = true;
    });

    // Save state on object modification
    canvas.on('object:modified', saveState);
    canvas.on('object:added', saveState);
}

// Save canvas state for undo
function saveState() {
    historyStack.push(JSON.stringify(canvas.toJSON()));
    if (historyStack.length > 20) {
        historyStack.shift();
    }
}

// Save strip to backend
async function saveStripToBackend() {
    const btn = document.getElementById('saveBtn');
    const originalHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

    try {
        // Convert canvas to base64 PNG
        const dataURL = canvas.toDataURL({
            format: 'png',
            quality: 1.0,
            multiplier: 1
        });

        // Send to backend
        const response = await axios.post('/api/strip/save', {
            session_id: sessionId,
            image: dataURL,
            metadata: {
                width: canvas.width,
                height: canvas.height,
                objects_count: canvas.getObjects().length
            }
        });

        if (response.data.success) {
            alert('Strip saved successfully! 🎉');

            // Ask if user wants to send via email
            const email = prompt('Enter email to receive your photos:');
            if (email) {
                await sendEmail(email);
            }

            // Redirect to preview
            setTimeout(() => {
                window.location.href = '/preview?session=' + sessionId;
            }, 1000);
        }
    } catch (error) {
        console.error('Error saving strip:', error);
        alert('Failed to save strip. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

// Send email
async function sendEmail(email) {
    try {
        const response = await axios.post('/api/photos/send-email', {
            session_id: sessionId,
            email: email
        });

        if (response.data.success) {
            alert('Email sent successfully! Check your inbox. 📧');
        }
    } catch (error) {
        console.error('Error sending email:', error);
        alert('Failed to send email');
    }
}
</script>
@endsection
