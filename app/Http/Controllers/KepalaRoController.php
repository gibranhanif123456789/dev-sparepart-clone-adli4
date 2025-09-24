<?php

namespace App\Http\Controllers;

use App\Models\Permintaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KepalaROController extends Controller
{
    // Dashboard: Daftar permintaan menunggu approval
    public function index()
    {
        $user = Auth::user(); // Kepala RO login

        $requests = Permintaan::with(['user', 'details'])
            ->where('status', 'pending')
            ->where('status_ro', '!=', 'approved')
            ->whereHas('user', function($q) use ($user) {
                $q->where('region', $user->region); // filter berdasarkan region
            })
            ->get();

        return view('kepalaro.dashboard', compact('requests'));
    }

    // History: Semua permintaan (disetujui/ditolak)
    public function history(Request $request)
    {
        $user = Auth::user();

        $query = Permintaan::with(['user', 'details'])
            ->whereHas('user', function($q) use ($user) {
                $q->where('region', $user->region);
            });

        // Filter 
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter tanggal (created_at atau tanggal_permintaan)
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('tanggal_permintaan', [
                $request->date_from,
                $request->date_to
            ]);
        }

        $requests = $query->orderByDesc('tanggal_permintaan')->get();

        return view('kepalaro.history', compact('requests'))
            ->with('filters', $request->only(['status', 'date_from', 'date_to']));
    }

    // Approve permintaan
    public function approve($id)
    {
        $user = Auth::user();

        $request = Permintaan::where('id', $id)
            ->whereHas('user', function($q) use ($user) {
                $q->where('region', $user->region);
            })
            ->where('status_ro', 'pending')
            ->firstOrFail();

        // ✅ Update status RO
        $request->status_ro = 'approved';
        $request->approved_by_ro = Auth::id();
        $request->status = 'pending'; // Tetap pending, karena belum sampai ke Super Admin

        // 🔥 SET NEXT STEP: Kepala Gudang → on progres
        $request->status_gudang = 'on progres';

        $request->save();

        return redirect()->back()->with('success', 'Permintaan disetujui dan diteruskan ke Kepala Gudang!');
    }

    // Reject permintaan
    public function reject($id)
    {
        $user = Auth::user();

        $request = Permintaan::where('id', $id)
            ->whereHas('user', function($q) use ($user) {
                $q->where('region', $user->region);
            })
            ->where('status_ro', 'pending')
            ->firstOrFail();

        // ✅ Update status RO
        $request->status_ro = 'rejected';
        $request->catatan_ro = $request->catatan_ro ?? 'Ditolak oleh Kepala RO';

        // 🔥 BROADCAST REJECTED KE SEMUA LEVEL
        $request->status_gudang = 'rejected';
        $request->status_admin = 'rejected';
        $request->status_super_admin = 'rejected';

        $request->save();

        return redirect()->back()->with('error', 'Permintaan ditolak dan proses dihentikan.');
    }
}