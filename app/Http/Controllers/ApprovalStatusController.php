<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permintaan;

class ApprovalStatusController extends Controller
{
    public function getStatus($tiket)
    {
        $permintaan = Permintaan::where('tiket', $tiket)->first();

        if (!$permintaan) {
            return response()->json(['message' => 'Tiket tidak ditemukan'], 404);
        }

        return response()->json([
            'ro'           => $permintaan->status_ro,
            'gudang'       => $permintaan->status_gudang,
            'admin'        => $permintaan->status_admin,
            'super_admin'  => $permintaan->status_super_admin,
              'status_barang' => $permintaan->status_barang, //
            'catatan'      => $this->getLastCatatan($permintaan),
        ]);
    }

    private function getLastCatatan($p)
    {
        // Urutkan dari paling akhir
        $catatans = [
            $p->catatan_super_admin,
            $p->catatan_admin,
            $p->catatan_gudang,
            $p->catatan_ro,
        ];

        foreach ($catatans as $catatan) {
            if ($catatan) return $catatan;
        }

        return null;
    }
}