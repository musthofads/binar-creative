# 📸 Laravel Photobooth System - Installation Guide

Production-ready Laravel 12 Photobooth System with Fabric.js Editor, QR Code, and Email Integration.

## 🚀 Features

- ✅ Session-based photo management
- ✅ Real-time camera capture (getUserMedia)
- ✅ Photo editor with Fabric.js (stickers, text, filters)
- ✅ QR code generation per session
- ✅ Email delivery via Resend API
- ✅ PostgreSQL database
- ✅ AWS S3 compatible storage
- ✅ Admin dashboard
- ✅ Role-based authentication

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- PostgreSQL 14+
- Node.js & NPM (optional, for Vite)
- Resend API Key (for emails)

## 🔧 Installation Steps

### 1. Clone & Install Dependencies

```bash
cd c:\laragon\www\proyekku

# Install PHP dependencies
composer require guzzlehttp/guzzle
composer require endroid/qr-code

# Optional: Install Laravel Sanctum for API auth
composer require laravel/sanctum
```

### 2. Environment Configuration

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Update `.env` with your settings:

```env
APP_NAME="MemoriesEnd Photobooth"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# PostgreSQL Database
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=photo_db
DB_USERNAME=postgres
DB_PASSWORD=your_secure_password

# Storage (use 'public' for local, 's3' for AWS)
FILESYSTEM_DISK=public

# For S3 Storage (optional)
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=your_access_key
# AWS_SECRET_ACCESS_KEY=your_secret_key
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=your-bucket-name
# AWS_USE_PATH_STYLE_ENDPOINT=false

# Mail Configuration (Resend)
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=re_YourResendAPIKey
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="support@photobooth-memoriesendxyz.online"
MAIL_FROM_NAME="${APP_NAME}"

# Resend API Key
RESEND_API_KEY=re_YourResendAPIKey

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=sync
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Run Migrations

```bash
php artisan migrate
```

This will create:
- `users` table (with role column)
- `photos` table
- `single_photos` table
- `strip_photo_originals` table
- `support_tickets` table
- `photobooth_sessions` table (new)

### 5. Seed Database

```bash
php artisan db:seed
```

This creates:
- **Admin User**: `admin@photobooth.local` / `admin123`
- **Guest User**: `guest@photobooth.local` / `guest123`

### 6. Storage Setup

```bash
# Create storage link
php artisan storage:link

# Create required directories
mkdir -p storage/app/public/photos
mkdir -p storage/app/public/qrcodes
mkdir -p storage/app/public/strips
```

### 7. Optimize for Production

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

## 📡 API Endpoints

### Session Management

```
POST   /api/session/create
GET    /api/session/{sessionId}
PATCH  /api/session/{sessionId}/activity
DELETE /api/session/{sessionId} (admin only)
```

### Photo Operations

```
POST   /api/photos/upload
GET    /api/photos?sessionId={sessionId}
POST   /api/strip/save
POST   /api/photos/send-email
```

### Authentication

```
POST   /api/register
POST   /api/login
POST   /api/logout
GET    /api/me
```

## 🎨 Frontend Routes

```
GET    /               - Camera page (home)
GET    /camera         - Photo capture
GET    /editor         - Fabric.js editor
GET    /preview        - Preview photos
GET    /strip          - View final strip
GET    /gallery        - Photo gallery
GET    /login          - Login page
GET    /register       - Register page
GET    /admin/*        - Admin dashboard (auth required)
```

## 📝 Usage Examples

### 1. Create New Session

```javascript
const response = await axios.post('/api/session/create', {
    metadata: {
        location: 'Event Hall',
        event: 'Birthday Party'
    }
});

const sessionId = response.data.session.session_id;
const qrCodeUrl = response.data.session.qr_code_url;
```

### 2. Upload Photo

```javascript
// Capture from camera
const canvas = document.getElementById('canvas');
const imageData = canvas.toDataURL('image/png');

const response = await axios.post('/api/photos/upload', {
    session_id: sessionId,
    image: imageData,
    metadata: {
        photo_number: 1
    }
});
```

### 3. Get Session Photos

```javascript
const response = await axios.get('/api/photos', {
    params: { sessionId: sessionId }
});

const photos = response.data.photos; // Array of photos
const strips = response.data.strips; // Array of strips
```

### 4. Save Edited Strip

```javascript
// From Fabric.js canvas
const canvas = new fabric.Canvas('canvas');
const dataURL = canvas.toDataURL({ format: 'png', quality: 1.0 });

const response = await axios.post('/api/strip/save', {
    session_id: sessionId,
    image: dataURL,
    metadata: {
        edited: true,
        objects_count: canvas.getObjects().length
    }
});
```

### 5. Send Email with QR

```javascript
const response = await axios.post('/api/photos/send-email', {
    session_id: sessionId,
    email: 'user@example.com'
});
```

## 🎭 Frontend Implementation

### Camera Capture (getUserMedia)

```javascript
// Start camera
const stream = await navigator.mediaDevices.getUserMedia({
    video: { 
        width: { ideal: 1280 },
        height: { ideal: 720 },
        facingMode: 'user'
    }
});
video.srcObject = stream;

// Capture photo
const canvas = document.getElementById('canvas');
const ctx = canvas.getContext('2d');
ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
const imageData = canvas.toDataURL('image/png');
```

### Fabric.js Editor Setup

```html
<!-- Include Fabric.js via CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>

<script>
// Initialize canvas
const canvas = new fabric.Canvas('canvas', {
    backgroundColor: '#ffffff',
    width: 1800,
    height: 1200
});

// Add text
const text = new fabric.Text('Hello!', {
    left: 100,
    top: 100,
    fontSize: 40,
    fill: '#000000'
});
canvas.add(text);

// Add emoji sticker
const emoji = new fabric.Text('😍', {
    left: 200,
    top: 200,
    fontSize: 80
});
canvas.add(emoji);

// Load image
fabric.Image.fromURL(imageUrl, function(img) {
    img.scaleToWidth(400);
    canvas.add(img);
});

// Export to PNG
const dataURL = canvas.toDataURL({ format: 'png' });
</script>
```

## 🔒 Security & Validation

All endpoints include:
- Input validation (Laravel Validator)
- Error handling with try-catch
- Session isolation (photos only accessible by session_id)
- CSRF protection
- SQL injection prevention (Eloquent ORM)
- XSS prevention (Blade escaping)

## 🌐 Deployment (Shared Hosting)

### For Hostinger/cPanel:

1. Upload files to `public_html`:
```
public_html/
├── index.php (from /public)
├── .htaccess
└── laravel/ (all other files)
```

2. Update `index.php`:
```php
require __DIR__.'/laravel/vendor/autoload.php';
$app = require_once __DIR__.'/laravel/bootstrap/app.php';
```

3. Set folder permissions:
```bash
chmod -R 755 storage bootstrap/cache
```

4. Configure `.htaccess`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

## 📊 Database Schema

### photobooth_sessions
```
id, session_id, qr_code_path, qr_code_url, user_id, 
photo_count, strip_generated, strip_path, metadata, 
last_activity, created_at, updated_at
```

### single_photos
```
id, url, user_id, session_id, storage_path, filename,
thumbnail_path, package_id, paid, amount_paid, metadata,
queue_number, created_at, updated_at
```

### photos (strips)
```
id, url, user_id, session_id, storage_path, filename,
thumbnail_path, package_id, paid, amount_paid, metadata,
queue_number, created_at, updated_at
```

## 🐛 Troubleshooting

### Issue: QR Code not generating
```bash
# Check if GD extension is enabled
php -m | grep gd

# Install if missing
sudo apt-get install php8.2-gd
```

### Issue: Email not sending
```bash
# Test Resend API key
curl -X POST 'https://api.resend.com/emails' \
  -H 'Authorization: Bearer YOUR_API_KEY' \
  -H 'Content-Type: application/json' \
  -d '{"from":"test@domain.com","to":"you@example.com","subject":"Test","html":"Test"}'
```

### Issue: Storage permission denied
```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

## 📞 Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Enable debug mode: `APP_DEBUG=true` in `.env`
- Check error logs in browser console

## 📄 License

MIT License

## 🎉 Credits

Built with:
- Laravel 12
- Fabric.js 5.3
- Endroid QR Code
- Guzzle HTTP Client
- Bootstrap 5
- Axios

---

**🚀 Ready to go! Start your photobooth at: `http://localhost` or your domain**
