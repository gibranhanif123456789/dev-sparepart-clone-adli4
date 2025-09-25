@extends('layouts.kepalaro')

@section('title', 'Histori Permintaan')

@section('content')
<div x-data="{
    showDetailModal: false,
    detailData: {},

    openDetail(data) {
        this.detailData = data;
        this.showDetailModal = true;
    }
}">
    <div class="py-8 px-6">
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Histori Permintaan</h2>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('kepalaro.history') }}" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-lg">
            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full border-gray-300 rounded-lg">
                    <option value="all" {{ ($filters['status'] ?? '') == 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="pending" {{ ($filters['status'] ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="diterima" {{ ($filters['status'] ?? '') == 'diterima' ? 'selected' : '' }}>Diterima</option>
                    <option value="ditolak" {{ ($filters['status'] ?? '') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <!-- Dari tanggal -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" id="date_from"
                       value="{{ $filters['date_from'] ?? '' }}"
                       class="w-full border-gray-300 rounded-lg">
            </div>

            <!-- Sampai tanggal -->
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" id="date_to"
                       value="{{ $filters['date_to'] ?? '' }}"
                       class="w-full border-gray-300 rounded-lg">
            </div>

            <!-- Tombol -->
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Filter</button>
                <a href="{{ route('kepalaro.history') }}"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">Reset</a>
            </div>
        </form>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($requests as $req)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">#{{ $req->id }}</td>
                            <td class="px-6 py-4 text-sm">{{ $req->user?->name ?? 'Tidak Diketahui' }}</td>
                            <td class="px-6 py-4 text-sm">{{ \Carbon\Carbon::parse($req->tanggal_permintaan)->format('d M Y') }}</td>
                            
                            <!-- 🔹 Kolom STATUS: Badge + Ikon Mata -->
                            <td class="px-6 py-4 text-sm flex items-center space-x-2">
                               <!-- Status Badge: Berdasarkan status_ro -->
<span class="px-2 py-1 text-xs rounded-full
    @if($req->status_ro === 'approved') bg-green-100 text-green-800
    @elseif($req->status_ro === 'rejected') bg-red-100 text-red-800
    @elseif($req->status_ro === 'on progres') bg-yellow-100 text-yellow-800
    @else bg-gray-100 text-gray-800 @endif">
    {{
        $req->status_ro === 'approved' ? 'Disetujui' :
        ($req->status_ro === 'rejected' ? 'Ditolak' :
        ($req->status_ro === 'on progres' ? 'On Progress' : 'Pending'))
    }}
</span>

                                <!-- Ikon Mata - Tracking Approval -->
                                <button 
                                    type="button"
                                    onclick="showStatusDetailModal('{{ $req->tiket }}', 'kepala_ro')"
                                    class="inline-flex items-center justify-center w-6 h-6 text-white bg-blue-600 hover:bg-blue-700 rounded-full transition duration-200 ease-in-out shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    title="Lihat progres approval">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            </td>

                            <!-- 🔹 Kolom AKSI: Hanya Tombol Detail -->
                            <td class="px-6 py-4 text-sm">
                                <button @click="openDetail({{ json_encode([
                                    'tiket' => $req->tiket,
                                    'user' => $req->user?->name,
                                    'email' => $req->user?->email,
                                    'tanggal' => \Carbon\Carbon::parse($req->tanggal_permintaan)->format('d M Y'),
                                    'status' => $req->status,
                                    'catatan' => $req->catatan ?? null,
                                    'details' => $req->details
                                ]) }})"
                                        class="text-blue-600 hover:text-blue-800 hover:underline text-sm font-medium">
                                    <i class="fas fa-info-circle me-1"></i> Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada riwayat permintaan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Detail (Alpine) -->
    <div x-show="showDetailModal"
         x-transition
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         x-cloak>
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-blue-600 text-white p-4 rounded-t-lg flex justify-between items-center">
                <h3 class="text-lg font-semibold">Detail Request</h3>
                <button @click="showDetailModal = false" class="text-white hover:text-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-sm text-gray-600">Nama Tiket:</p>
                        <p class="font-medium" x-text="detailData.tiket"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">User:</p>
                        <p class="font-medium" x-text="detailData.user"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal:</p>
                        <p class="font-medium" x-text="detailData.tanggal"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status:</p>
                        <span class="px-2 py-1 text-xs rounded-full ml-1"
                              :class="{
                                  'bg-green-100 text-green-800': detailData.status === 'diterima',
                                  'bg-red-100 text-red-800': detailData.status === 'ditolak',
                                  'bg-gray-100 text-gray-800': detailData.status !== 'diterima' && detailData.status !== 'ditolak'
                              }"
                              x-text="detailData.status ? detailData.status.charAt(0).toUpperCase() + detailData.status.slice(1) : '-'">
                        </span>
                    </div>
                </div>
                <template x-if="detailData.status === 'ditolak' && detailData.catatan">
                    <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-800">
                        <strong>Catatan Penolakan:</strong>
                        <span x-text="detailData.catatan"></span>
                    </div>
                </template>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 text-sm">
                        <thead class="bg-blue-50 text-blue-700">
                            <tr>
                                <th class="px-4 py-2 border-b text-left">No</th>
                                <th class="px-4 py-2 border-b text-left">Nama Item</th>
                                <th class="px-4 py-2 border-b text-left">Deskripsi</th>
                                <th class="px-4 py-2 border-b text-left">Jumlah</th>
                                <th class="px-4 py-2 border-b text-left">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="detailData.details && detailData.details.length > 0">
                                <template x-for="(item, index) in detailData.details" :key="item.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 border-b text-center" x-text="index + 1"></td>
                                        <td class="px-4 py-2 border-b" x-text="item.nama_item"></td>
                                        <td class="px-4 py-2 border-b" x-text="item.deskripsi"></td>
                                        <td class="px-4 py-2 border-b text-center" x-text="item.jumlah"></td>
                                        <td class="px-4 py-2 border-b" x-text="item.keterangan || '-'"></td>
                                    </tr>
                                </template>
                            </template>
                            <tr x-show="!detailData.details || detailData.details.length === 0">
                                <td colspan="5" class="px-4 py-2 text-center text-gray-500">Tidak ada item.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end">
                <button @click="showDetailModal = false"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded text-sm font-medium transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Include Komponen Modal Tracking -->
@include('components.tracking-modal')

@endsection