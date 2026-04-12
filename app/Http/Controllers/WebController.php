<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\PhotoboothSession;
use Illuminate\Support\Facades\Auth;

class WebController extends Controller
{
    // Menampilkan halaman kamera saja
    public function camera()
    {
        // Cek apakah sudah ada session_id, jika belum biarkan null dulu
        $sessionId = session()->get('photo_session_id');
        $requiredPhotos = 100; // Bisa dibuat dinamis nanti berdasarkan paket

        return view('camera', compact('sessionId', 'requiredPhotos'));
    }

    /**
     * Show editor page.
     */
    public function editor()
    {
        $sessionId = session()->get('photo_session_id');

        if (!$sessionId) {
            return redirect()->route('camera');
        }

        return view('editor', compact('sessionId'));
    }

    /**
     * Show preview page.
     */
    public function preview()
    {
        $sessionId = session()->get('photo_session_id');

        if (!$sessionId) {
            return redirect()->route('camera');
        }

        return view('preview', compact('sessionId'));
    }

    /**
     * Show strip page.
     */
    public function strip()
    {
        $sessionId = session()->get('photo_session_id');

        if (!$sessionId) {
            return redirect()->route('camera');
        }

        return view('strip', compact('sessionId'));
    }

    /**
     * Show gallery page.
     */
    public function gallery()
    {
        return view('gallery');
    }

    /**
     * Show login page.
     */
    public function login()
    {
        return view('auth.login');
    }

    /**
     * Show register page.
     */
    public function register()
    {
        return view('auth.register');
    }
}
