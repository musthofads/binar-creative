<?php
namespace App\Http\Controllers;

use App\Models\PhotoboothSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PublicGalleryController extends Controller
{
    public function show($session_id)
    {
        $session = PhotoboothSession::with('photos')->where('session_id', $session_id)->firstOrFail();

        // Jika password diisi di database, cek apakah user sudah memasukkannya di session
        if (!empty($session->access_password)) {
            if (Session::get("access_granted_{$session_id}") !== true) {
                return view('public.auth', compact('session'));
            }
        }

        return view('public.gallery', compact('session'));
    }

    public function verify(Request $request, $session_id)
    {
        $session = PhotoboothSession::where('session_id', $session_id)->firstOrFail();

        // Validasi password sederhana (case-sensitive)
        if ($request->password === $session->access_password) {
            Session::put("access_granted_{$session_id}", true);
            return redirect()->route('public.gallery.show', $session_id);
        }

        return back()->with('error', 'Opps! Password yang Anda masukkan salah.');
    }
}
