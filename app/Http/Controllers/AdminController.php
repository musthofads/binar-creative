<?php
namespace App\Http\Controllers;

// Gunakan controller dasar Laravel
use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\PhotoboothSession;
use App\Models\SinglePhoto;
use App\Models\SupportTicket;
use App\Models\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Show admin gallery.
     */
    public function gallery(Request $request)
    {
        $search = $request->query('search');

        $sessions = PhotoboothSession::with('photos') // Eager load untuk optimasi gambar
            ->when($search, function ($query, $search) {
                return $query->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('package_type', 'like', "%{$search}%")
                            ->orWhere('created_at', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString(); // PENTING: Menjaga parameter ?search tetap ada di link pagination

        // 3. Kirim ke view
        return view('admin.gallery', compact('sessions'));
    }

   /**
     * Menghasilkan QR Code dalam format Base64.
     */
    protected function generateQrCodeBase64(string $qrContent): ?string
    {
        $qrCode = new QrCode(
            data: $qrContent,
            encoding: new Encoding('UTF-8'),
            size: 350,
            margin: 10
        );

        $writer = new PngWriter();

        $logo = $this->getQrCodeLogo();

        $result = $writer->write(
            qrCode: $qrCode,
            logo: $logo,
            label: null
        );

        return sprintf(
            'data:%s;base64,%s',
            $result->getMimeType(),
            base64_encode($result->getString())
        );
    }

    /**
     * Mendapatkan object Logo untuk QR Code.
     */
    protected function getQrCodeLogo(): ?Logo
    {
        $logoPath = public_path('assets/images/icon-logo.png');

        if (file_exists($logoPath)) {
            return new Logo(
                path: $logoPath,
                resizeToWidth: 75,
                resizeToHeight: null,
                punchoutBackground: false
            );
        }

        return null;
    }

    public function showGallery(Request $request, $id)
    {
        $session = PhotoboothSession::findOrFail($id);

        $publicUrl = route('public.gallery.show', $session->session_id);

        $qrCodeData = $this->generateQrCodeBase64($publicUrl);

        $photos = $session->photos()
            ->latest()
            ->paginate(9);

        if ($request->ajax()) {
            return response()->json([
                'html'    => view('partials.photo-items', compact('photos', 'session'))->render(),
                'hasMore' => $photos->hasMorePages(),
            ]);
        }

        // 4. Kirim qrCodeDataUri ke View
        return view('admin.gallery-show', compact('session', 'photos', 'qrCodeData'));
    }

    public function destroy($id)
    {
        try {
            $session = PhotoboothSession::findOrFail($id);

            // Hapus file fisik
            foreach ($session->photos as $photo) {
                Storage::disk('public')->delete($photo->storage_path);
            }

            $session->delete();

            return response()->json([
                'success' => true,
                'message' => 'Session and photos deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!'
            ], 500);
        }
    }

    /**
     * Show admin dashboard.
     */
    public function dashboard()
    {
        Log::info('Dashboard accessed by User ID: ' . Auth::id());

        try {
            $stats = [
                // Package Stats
                'pkg_basic'       => PhotoboothSession::where('package_type', 'BASIC')->count(),
                'pkg_bestie'      => PhotoboothSession::where('package_type', 'BESTIE')->count(),
                'pkg_ramean'      => PhotoboothSession::where('package_type', 'RAMEAN')->count(),
            ];

            // Daily Activity (Last 7 Days)
            $daily_stats = PhotoboothSession::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->take(7)
                ->get();

        } catch (\Exception $e) {
            Log::error('Error loading dashboard stats: ' . $e->getMessage());
            $stats = array_fill_keys(['pkg_basic', 'pkg_bestie', 'pkg_ramean'], 0);
            $daily_stats = collect();
        }

        return view('admin.dashboard', compact('stats', 'daily_stats'));
    }

    // /**
    //  * Show users management.
    //  */
    // public function users()
    // {
    //     // Gunakan simplePaginate jika data sangat besar, atau paginate biasa.
    //     $users = User::orderBy('created_at', 'desc')->paginate(20);
    //     return view('admin.users', compact('users'));
    // }

    // /**
    //  * Show support tickets.
    //  */
    // public function tickets()
    // {
    //     // Pastikan relasi 'user' sudah didefinisikan di model SupportTicket
    //     $tickets = SupportTicket::with('user')
    //         ->orderBy('created_at', 'desc')
    //         ->paginate(20);

    //     return view('admin.tickets', compact('tickets'));
    // }

    // /**
    //  * Delete user.
    //  */
    // public function deleteUser($id)
    // {
    //     $user = User::find($id);

    //     if (!$user) {
    //         return redirect()->back()->with('error', 'User not found.');
    //     }

    //     // PERBAIKAN: Pastikan membandingkan ID dengan tipe data yang benar
    //     if ((int)$user->id === (int)Auth::id()) {
    //         return redirect()->back()->with('error', 'You cannot delete your own account.');
    //     }

    //     try {
    //         $user->delete();
    //         return redirect()->back()->with('success', 'User deleted successfully.');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'Failed to delete user.');
    //     }
    // }

    // // Method kosong tetap dibiarkan selama view-nya ada
    // public function photos() { return view('admin.photos'); }
    // public function sessions() { return view('admin.sessions'); }
}
