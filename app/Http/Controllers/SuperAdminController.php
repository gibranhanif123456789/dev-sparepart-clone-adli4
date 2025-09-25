<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Permintaan;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\DetailBarang;

class SuperAdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        $groups = DB::table('detail_barang')
            ->select('jenis_id', 'tipe_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('MAX(id) as latest_id'))
            ->whereDate('tanggal', $date)
            ->groupBy('jenis_id', 'tipe_id')
            ->get();

        if ($groups->isEmpty()) {
            $detail = collect();
            $totalPerDay = 0;
            return view('superadmin.dashboard', compact('detail', 'date', 'totalPerDay'));
        }

        $latestIds = $groups->pluck('latest_id')->filter()->all();

        $latestRecords = DetailBarang::with(['jenis', 'tipe'])
            ->whereIn('id', $latestIds)
            ->get()
            ->keyBy('id');

        $rows = $groups->map(function ($g) use ($latestRecords, $date) {
            $r = $latestRecords->get($g->latest_id);

            $jenis_nama = $r && $r->jenis ? $r->jenis->nama : null;
            $tipe_nama = $r && $r->tipe ? $r->tipe->nama : null;

            return (object) [
                'id' => $g->latest_id,
                'tiket_sparepart' => $r ? $r->tiket_sparepart : null,
                'nama_barang' => $r ? $r->nama_barang : null,
                'qty_record' => $r ? $r->quantity : 0,
                'jenis' => $r ? $r->jenis : (object) ['id' => $g->jenis_id, 'nama' => $jenis_nama],
                'tipe' => $r ? $r->tipe : (object) ['id' => $g->tipe_id, 'nama' => $tipe_nama],
                'jenis_nama' => $jenis_nama,
                'tipe_nama' => $tipe_nama,
                'total_qty' => (int) $g->total_qty,
                'tanggal' => $r ? $r->tanggal : $date,
            ];
        })->sortByDesc('tiket_sparepart')->values();

        $detail = $rows;
        $totalPerDay = $groups->sum('total_qty');
        $totalMasuk = DetailBarang::whereDate('tanggal', $date)->sum('quantity');

        return view('superadmin.dashboard', compact('detail', 'date', 'totalPerDay', 'totalMasuk'));
    }

    public function requestIndex()
{
    $user = Auth::user();

    if ($user->id === 15) {
        // Admin (Mbak Inong): tampilkan jika belum diproses
        $requests = Permintaan::with(['user', 'details'])
            ->where('status_ro', 'approved')
            ->where('status_gudang', 'approved')
            ->where('status_admin', '!=', 'approved')   // ✅ Bukan approved
            ->where('status_admin', '!=', 'rejected')   // ✅ Bukan rejected
            ->orderBy('tanggal_permintaan', 'desc')
            ->paginate(10);
    } 
    elseif ($user->id === 16) {
        // Super Admin (Mas Septian): tampilkan jika Admin sudah approve
        $requests = Permintaan::with(['user', 'details'])
            ->where('status_admin', 'approved')
            ->where('status_super_admin', '!=', 'approved')   // ✅ Belum disetujui
            ->where('status_super_admin', '!=', 'rejected')   // ✅ Belum ditolak
            ->orderBy('tanggal_permintaan', 'desc')
            ->paginate(10);
    } 
    else {
        $requests = new LengthAwarePaginator([], 0, 10);
    }

    return view('superadmin.request', compact('requests'));
}

public function historyIndex(Request $request)
{
    $user = Auth::user();

    $query = Permintaan::with(['user', 'details', 'pengiriman.details'])
        ->where(function ($q) use ($user) {
            // Untuk Admin (ID 15): tampilkan yang dia approve/reject
            if ($user->id === 15) {
                $q->where('status_admin', 'approved')
                  ->orWhere('status_admin', 'rejected');
            }
            // Untuk Super Admin (ID 16): tampilkan yang dia approve/reject
            elseif ($user->id === 16) {
                $q->where('status_super_admin', 'approved')
                  ->orWhere('status_super_admin', 'rejected');
            }
        })
        ->orWhereHas('pengiriman') // atau sudah dikirim
        ->orderByDesc('tanggal_permintaan');

    // Filter tanggal
    if ($request->filled('dateFrom')) {
        $query->whereDate('tanggal_permintaan', '>=', $request->input('dateFrom'));
    }
    if ($request->filled('dateTo')) {
        $query->whereDate('tanggal_permintaan', '<=', $request->input('dateTo'));
    }

    $requests = $query->distinct()->paginate(10)->withQueryString();

    return view('superadmin.history', compact('requests'));
}
    /**
     * Approve oleh Admin (Mbak Inong) atau Super Admin (Mas Septian)
     */
    public function approveRequest(Request $request)
    {
        $tiket = $request->tiket;
        $user = Auth::user();

        // 🔹 ADMIN (Mbak Inong) - ID 15
        if ($user->id === 15) {
            $permintaan = Permintaan::where('tiket', $tiket)->firstOrFail();

            if ($permintaan->status_ro !== 'approved' || $permintaan->status_gudang !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Permintaan belum disetujui oleh RO/Gudang.'
                ], 400);
            }

            // ✅ Set status admin + next step: Super Admin → on progres
            $permintaan->update([
                'status_admin' => 'approved',
                'status_super_admin' => 'on progres',
                'approved_by_admin' => $user->id,
                'catatan_admin' => $request->catatan ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan disetujui oleh Admin (Mbak Inong). Telah dikirim ke Super Admin.'
            ]);
        }

        // 🔹 SUPER ADMIN (Mas Septian) - ID 16
        if ($user->id === 16) {
            $permintaan = Permintaan::where('tiket', $tiket)->firstOrFail();

            if ($permintaan->status_admin !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Permintaan belum disetujui oleh Admin.'
                ], 400);
            }

            // ✅ Final approve → close semua status
            $permintaan->update([
                'status_super_admin' => 'approved',
                'approved_by_super_admin' => $user->id,
                'catatan_super_admin' => $request->catatan ?? null,
                'status_barang' => 'diterima',
            ]);

            // 🔥 Opsional: close semua status (jika ingin konsisten)
            // $this->closeAllStatus($permintaan);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan disetujui final oleh Super Admin (Mas Septian). Barang siap dikirim.'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    /**
     * Close semua status menjadi 'close'
     */
    private function closeAllStatus($permintaan)
    {
        $permintaan->update([
            'status_ro' => 'close',
            'status_gudang' => 'close',
            'status_admin' => 'close',
            'status_super_admin' => 'close',
        ]);
    }

    /**
     * Tolak permintaan → broadcast rejected ke semua level
     */
    public function reject(Request $request)
    {
        $tiket = $request->tiket;
        $user = Auth::user();

        // 🔹 ADMIN (Mbak Inong) - ID 15
        if ($user->id === 15) {
            $permintaan = Permintaan::where('tiket', $tiket)->firstOrFail();

            if ($permintaan->status_ro !== 'approved' || $permintaan->status_gudang !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Permintaan belum disetujui oleh RO/Gudang.'
                ], 400);
            }

            // 🔥 Broadcast rejected
            $permintaan->update([
                'status_admin' => 'rejected',
                'status_super_admin' => 'rejected',
                'status_gudang' => 'rejected',
                'status_ro' => 'rejected',
                'status_barang'        => 'rejected',
                'approved_by_admin' => $user->id,
                'catatan_admin' => $request->catatan ?? 'Ditolak oleh Admin (Mbak Inong)',
                'status' => 'ditolak',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan ditolak oleh Admin.'
            ]);
        }

        // 🔹 SUPER ADMIN (Mas Septian) - ID 16
        if ($user->id === 16) {
            $permintaan = Permintaan::where('tiket', $tiket)->firstOrFail();

            if ($permintaan->status_admin !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Permintaan belum disetujui oleh Admin.'
                ], 400);
            }

            // 🔥 Broadcast rejected
            $permintaan->update([
                'status_super_admin' => 'rejected',
                'status_admin' => 'rejected',
                'status_gudang' => 'rejected',
                'status_ro' => 'rejected',
                'status_barang'        => 'rejected',
                'approved_by_super_admin' => $user->id,
                'catatan_super_admin' => $request->catatan ?? 'Ditolak oleh Super Admin (Mas Septian)',
                'status' => 'ditolak',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan ditolak oleh Super Admin.'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    public function historyDetailApi($tiket)
{
    try {
        $permintaan = Permintaan::with(['user', 'details', 'pengiriman.details'])
            ->where('tiket', $tiket)
            ->firstOrFail();

        return response()->json([
            'permintaan' => [
                'tiket' => $permintaan->tiket,
                'user' => $permintaan->user,
                'tanggal_permintaan' => $permintaan->tanggal_permintaan,
                'details' => $permintaan->details,
            ],
            'pengiriman' => $permintaan->pengiriman ? [
                'tanggal_transaksi' => $permintaan->pengiriman->tanggal_transaksi,
                'details' => $permintaan->pengiriman->details
            ] : null
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Data tidak ditemukan'], 404);
    }
}
}