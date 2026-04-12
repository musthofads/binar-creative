<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\PhotoUploadController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\QrController;

// Public API routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ===== NEW PRODUCTION ENDPOINTS =====

Route::post('/init-session', [SessionController::class, 'initSession']);

// Session management
Route::post('/session/create', [SessionController::class, 'create']);
Route::get('/session/{sessionId}', [SessionController::class, 'show']);
Route::patch('/session/{sessionId}/activity', [SessionController::class, 'updateActivity']);

// Photo upload (session-based, no auth required)
Route::post('/photos/upload', [PhotoUploadController::class, 'upload']);
Route::get('/photos', [PhotoUploadController::class, 'getPhotosBySession']); // ?sessionId=xxx
Route::post('/strip/save', [PhotoUploadController::class, 'saveStrip']); // Manual edited strip from canvas
Route::post('/strip/generate', [PhotoUploadController::class, 'generateStrip']); // Auto-generate based on template
Route::post('/photos/send-email', [PhotoUploadController::class, 'sendEmail']);

// ===== LEGACY ENDPOINTS (keep for compatibility) =====
Route::post('/single-photo', [PhotoController::class, 'saveSinglePhoto']);
Route::post('/save-strip', [PhotoController::class, 'saveStrip']);
Route::get('/queue', [PhotoController::class, 'getQueue']);
Route::post('/queue/{id}', [PhotoController::class, 'addToQueue']);

// Support routes
Route::post('/support', [SupportController::class, 'createTicket']);

// QR code routes
Route::post('/send-qr', [QrController::class, 'sendQr']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // User's own tickets
    Route::get('/my-tickets', [SupportController::class, 'getMyTickets']);
});

// Admin only routes
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get('/sessions', [PhotoController::class, 'getSessions']);
    Route::get('/photos-admin', [PhotoController::class, 'getAllPhotos']);
    Route::delete('/photos/{id}', [PhotoController::class, 'deletePhoto']);
    Route::delete('/session/{sessionId}', [SessionController::class, 'destroy']);

    // Support tickets management
    Route::get('/tickets', [SupportController::class, 'getAllTickets']);
    Route::patch('/tickets/{id}', [SupportController::class, 'updateTicketStatus']);
});
