# 📸 MemoriesEnd Photobooth

A modern, feature-rich photobooth web application built with Laravel 12 that allows users to capture memorable moments, create photo strips, and share them instantly.

![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Database-336791?style=flat&logo=postgresql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.2-7952B3?style=flat&logo=bootstrap&logoColor=white)

## ✨ Features

### Core Features
- 📷 **Live Camera Capture** - Real-time webcam access with countdown timer
- 🎨 **Photo Editor** - Add stickers, text, filters, and frames to your photos
- 🖼️ **Strip Generator** - Automatically generate beautiful photo strips from 4 photos
- 📧 **Email Sharing** - Send photos directly to email with QR codes
- 🔗 **QR Code Generation** - Generate QR codes for easy photo access
- 👥 **Session Management** - Track photobooth sessions and photo counts
- 🎭 **Gallery View** - Browse all captured moments
- 🔐 **User Authentication** - Secure login and registration system
- 👨‍💼 **Admin Dashboard** - Manage users, photos, sessions, and support tickets

### Technical Features
- 🎯 Session-based photo organization
- 🖼️ Base64 image handling for instant preview
- 📱 Responsive design for all devices
- 🎨 Modern UI with gradient backgrounds and animations
- ⚡ Real-time photo upload and processing
- 🔄 Image mirroring for natural selfie experience
- 💾 Persistent storage with PostgreSQL
- 🎪 Flash effect on photo capture
- 🗑️ Photo retake and deletion options

## 🛠️ Tech Stack

### Backend
- **Framework:** Laravel 12.0
- **PHP Version:** 8.2+
- **Database:** PostgreSQL
- **Queue System:** Database-based
- **Storage:** Local file system (public disk)

### Frontend
- **CSS Framework:** Bootstrap 5.3.2
- **Icons:** Bootstrap Icons 1.11.0
- **Animations:** Animate.css 4.1.1
- **Fonts:** Google Fonts (Inter)
- **HTTP Client:** Axios
- **Build Tool:** Vite 7.0.7

### Libraries & Dependencies

#### PHP Dependencies
```json
{
    "endroid/qr-code": "^6.0",        // QR code generation
    "guzzlehttp/guzzle": "^7.10",     // HTTP client for API calls
    "laravel/framework": "^12.0",      // Laravel framework
    "laravel/tinker": "^2.10.1"        // Laravel REPL
}
```

#### JavaScript Dependencies
```json
{
    "axios": "^1.11.0",                       // Promise-based HTTP client
    "@tailwindcss/vite": "^4.0.0",            // Tailwind CSS for Vite
    "laravel-vite-plugin": "^2.0.0",          // Laravel Vite integration
    "vite": "^7.0.7"                          // Frontend build tool
}
```

### CDN Resources Used
```html
<!-- CSS -->
Bootstrap 5.3.2       : https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css
Bootstrap Icons 1.11.0: https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css
Animate.css 4.1.1     : https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css
Google Fonts (Inter)  : https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800

<!-- JavaScript -->
Bootstrap JS 5.3.2    : https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js
Axios                 : https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js
```

## 📋 Requirements

- PHP >= 8.2
- PostgreSQL >= 12.0
- Composer
- Node.js >= 18.x
- NPM or Yarn
- Web server (Apache/Nginx) or Laravel Artisan

## 🚀 Installation

### 1. Clone Repository
```bash
git clone <repository-url>
cd proyekku
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node Dependencies
```bash
npm install
```

### 4. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Database
Edit `.env` file with your PostgreSQL credentials:
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=photo_db
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 6. Run Migrations
```bash
php artisan migrate
```

### 7. Create Storage Link
```bash
php artisan storage:link
```

### 8. Build Assets
```bash
npm run build
```

## 🎮 Running the Application

### Development Mode

#### Option 1: Using Laravel's Built-in Server
```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite dev server
npm run dev
```

#### Option 2: Using Composer Script (Recommended)
```bash
composer run dev
```
This command runs:
- Laravel development server (localhost:8000)
- Queue listener
- Log viewer (Pail)
- Vite development server

### Production Mode
```bash
# Build assets for production
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start server (use a process manager like Supervisor in production)
php artisan serve --host=0.0.0.0 --port=8000
```

## 📁 Project Structure

```
proyekku/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php
│   │   │   ├── AuthController.php
│   │   │   ├── PhotoController.php
│   │   │   ├── PhotoUploadController.php
│   │   │   ├── QrController.php
│   │   │   ├── SessionController.php
│   │   │   ├── SupportController.php
│   │   │   └── WebController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── Photo.php
│   │   ├── PhotoboothSession.php
│   │   ├── SinglePhoto.php
│   │   ├── StripPhotoOriginal.php
│   │   ├── SupportTicket.php
│   │   └── User.php
│   ├── Services/
│   │   ├── EmailService.php
│   │   ├── PhotoService.php
│   │   ├── QrCodeService.php
│   │   └── StripGeneratorService.php
│   └── Providers/
│       └── AppServiceProvider.php
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── storage/           # Symlinked storage folder
│   └── index.php
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.js
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── admin/
│       ├── auth/
│       ├── emails/
│       ├── camera.blade.php
│       ├── editor.blade.php
│       ├── gallery.blade.php
│       ├── preview.blade.php
│       └── strip.blade.php
├── routes/
│   ├── api.php            # API routes
│   ├── web.php            # Web routes
│   └── console.php
├── storage/
│   └── app/
│       └── public/        # Public file storage
│           ├── photos/
│           ├── strips/
│           └── qrcodes/
├── .env.example
├── composer.json
├── package.json
└── vite.config.js
```

## 🔧 Configuration

### Email Service (Optional)
To enable email functionality, add to `.env`:
```env
RESEND_API_KEY=your_resend_api_key
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### File Storage
Photos are stored in `storage/app/public/`. Make sure the storage link is created:
```bash
php artisan storage:link
```

### Session Configuration
```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

## 🎯 Usage

### For Users
1. **Access Camera** - Navigate to `/camera` or home page
2. **Take Photos** - Click "Take Photo" button (captures 4 photos with countdown)
3. **Review Photos** - Check thumbnails after each capture
4. **Edit (Optional)** - Add stickers, text, or filters in the editor
5. **Generate Strip** - Click "Generate Strip" to create your photo strip
6. **Share** - Download or email your photo strip

### For Admins
1. **Login** - Access admin dashboard at `/admin/dashboard`
2. **Manage Photos** - View and manage all captured photos
3. **Manage Sessions** - Track photobooth sessions
4. **Manage Users** - View and manage registered users
5. **Support Tickets** - Handle user support requests

## 📡 API Endpoints

### Public Endpoints
```
POST   /api/register              # User registration
POST   /api/login                 # User login
POST   /api/session/create        # Create photobooth session
GET    /api/session/{sessionId}   # Get session details
POST   /api/photos/upload         # Upload photo
GET    /api/photos                # Get photos by session
POST   /api/strip/save            # Save photo strip
POST   /api/photos/send-email     # Send photos via email
POST   /api/support               # Create support ticket
```

### Protected Endpoints (Requires Authentication)
```
POST   /api/logout                # Logout user
GET    /api/me                    # Get current user
GET    /api/my-tickets            # Get user's support tickets
```

## 🎨 Features in Detail

### Camera Module
- Live webcam preview with mirror effect
- 3-second countdown timer
- Flash effect on capture
- Progress indicators (1/4, 2/4, 3/4, 4/4)
- Photo thumbnails with delete option
- Start Over functionality

### Editor Module
- Add stickers and emojis
- Add custom text with color options
- Apply filters (B&W, Sepia, Vintage, etc.)
- Add decorative frames
- Real-time preview
- Save and continue to strip generation

### Strip Generator
- Automatic 4-photo strip layout
- Professional design templates
- High-quality output
- Instant preview
- Download option

### Admin Dashboard
- Statistics overview
- Photo management
- Session tracking
- User management
- Support ticket system

## 🐛 Troubleshooting

### Camera Not Working
- Ensure HTTPS or localhost (browsers require secure context for camera access)
- Check browser permissions for camera access
- Try different browsers (Chrome/Firefox recommended)

### Photos Not Saving
- Check `storage/app/public/` directory permissions (755)
- Ensure storage link exists: `php artisan storage:link`
- Check database connection in `.env`

### Assets Not Loading
- Run `npm run build`
- Clear browser cache
- Check `public/build/` directory exists

### Database Connection Failed
- Verify PostgreSQL is running
- Check `.env` database credentials
- Test connection: `php artisan tinker` then `DB::connection()->getPdo();`

## 🔒 Security

- CSRF protection on all forms
- SQL injection prevention via Eloquent ORM
- XSS protection with Blade templating
- Session-based authentication
- Sanctum API authentication
- Input validation on all requests

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📝 License

This project is open-source and available under the [MIT License](LICENSE).

## 👨‍💻 Developer Notes

### Service Layer Architecture
- `PhotoService`: Handles photo upload and storage
- `StripGeneratorService`: Generates photo strips
- `EmailService`: Manages email sending via Resend API
- `QrCodeService`: Generates QR codes for photos

### Database Schema
- `users`: User authentication
- `photobooth_sessions`: Session tracking
- `single_photos`: Individual captured photos
- `photos`: Generated photo strips
- `strip_photo_originals`: Original photos used in strips
- `support_tickets`: User support requests

### Browser Compatibility
- Chrome 90+ (Recommended)
- Firefox 88+
- Safari 14+
- Edge 90+

## 📞 Support

For issues and questions:
- Create an issue in the repository
- Contact support through the app's support ticket system

## 🎉 Acknowledgments

- Laravel Framework
- Bootstrap Team
- Endroid QR Code Library
- All contributors and users

---

**Made with ❤️ using Laravel**

- **Backend**: Laravel 12, PHP 8.2+
- **Database**: PostgreSQL 14+
- **Storage**: Local/S3
- **Email**: Resend API (Guzzle)
- **QR Codes**: Endroid QR Code
- **Frontend**: Blade, Fabric.js 5.3, Bootstrap 5, Axios
- **Auth**: Laravel Sanctum

## 🔒 Security Features

- ✅ Input validation (Laravel Validator)
- ✅ Session isolation
- ✅ CSRF protection
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ Error handling

## 📚 Full Documentation

See [INSTALLATION.md](INSTALLATION.md) for:
- Complete installation steps
- Environment configuration
- API usage examples
- Frontend integration guide
- Deployment instructions
- Troubleshooting

## 🌐 About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
