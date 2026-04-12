<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [WebController::class, 'camera'])->name('home');
Route::get('/camera', [WebController::class, 'camera'])->name('camera');
Route::get('/editor', [WebController::class, 'editor'])->name('editor');
Route::get('/preview', [WebController::class, 'preview'])->name('preview');
Route::get('/strip', [WebController::class, 'strip'])->name('strip');

// Auth routes
Route::get('/login', [WebController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return response()->json(['success' => true]);
})->name('logout');

// Admin routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/gallery', [AdminController::class, 'gallery'])->name('gallery');
    Route::get('/gallery/{id}', [AdminController::class, 'showGallery'])->name('gallery.show');
    // Route::get('/photos', [AdminController::class, 'photos'])->name('photos');
    // Route::get('/sessions', [AdminController::class, 'sessions'])->name('sessions');
    // Route::get('/users', [AdminController::class, 'users'])->name('users');
    // Route::get('/tickets', [AdminController::class, 'tickets'])->name('tickets');
    // Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
});
