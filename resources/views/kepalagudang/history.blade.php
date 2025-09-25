@extends('layouts.kepalagudang')

@section('title', 'History Sparepart - Kepalagudang')

@push('styles')
@endpush

@section('content')

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Histori Transaksi</h4>
                <p class="text-muted mb-0">Riwayat barang masuk & keluar gudang</p>
            </div>
            <a href="{{ route('kepalagudang.dashboard') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="filter-card">
        <h5 class="mb-4"><i class="bi bi-funnel me-2"></i>Filter Data</h5>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="dateFrom" class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" id="dateFrom">
            </div>
            <div class="col-md-3 mb-3">
                <label for="dateTo" class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" id="dateTo">
            </div>
            <div class="col-md-3 mb-3">
                <label for="jenisFilter" class="form-label">Jenis</label>
                <select class="form-select" id="jenisFilter">
                    <option value="">Semua Jenis</option>
                    <option value="masuk">Masuk</option>
                    <option value="keluar">Keluar</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="statusFilter" class="form-label">Status</label>
                <select class="form-select" id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="dikirim">Dikirim</option>
                    <option value="diterima">Diterima</option>
                    <option value="diproses">Diproses</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <button class="btn btn-light me-2">
                <i class="bi bi-arrow-clockwise me-1"></i> Reset
            </button>
            <button class="btn btn-primary">
                <i class="bi bi-search me-1"></i> Terapkan Filter
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-export">
            <i class="bi bi-download me-1"></i> Export Data
        </button>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Requester</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                        <tr>
                            <td><span class="fw-bold">{{ $req->tiket }}</span></td>
                            <td>{{ $req->user->name ?? '-' }}</td>
                           <td class="d-flex align-items-center gap-2">
    <!-- Status Badge -->
    @php
        $status = '';
        if ($req->status_gudang === 'approved') {
            $status = 'Diterima';
        } elseif ($req->status_gudang === 'rejected') {
            $status = 'Ditolak';
        } elseif ($req->status_gudang === 'on progres') {
            $status = 'On Progress';
        } elseif ($req->status_gudang === 'pending' && $req->status_ro === 'approved') {
            $status = 'On Progress';
        } else {
            $status = 'Pending';
        }
    @endphp

    <span class="badge 
        @if($req->status_gudang === 'approved') bg-success
        @elseif($req->status_gudang === 'rejected') bg-danger
        @elseif($req->status_gudang === 'on progres') bg-warning text-dark
        @elseif($req->status_gudang === 'pending' && $req->status_ro === 'approved') bg-warning text-dark
        @else bg-secondary @endif">
        {{ $status }}
    </span>


                                <!-- 🔹 Ikon Mata - Tracking Approval -->
                                <button 
                                    type="button"
                                    onclick="showStatusDetailModal('{{ $req->tiket }}', 'kepala_gudang')"
                                    class="inline-flex items-center justify-center w-6 h-6 text-white bg-blue-600 hover:bg-blue-700 rounded-full transition focus:outline-none"
                                    title="Lihat progres approval">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($req->tanggal_permintaan)->format('Y-m-d') }}</td>
                            <td>
                                <button class="btn btn-info btn-sm btn-history" data-bs-toggle="modal"
                                    data-bs-target="#modalHistory" data-tiket="{{ $req->tiket }}">
                                    <i class="bi bi-clock-history"></i> History
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-container d-flex justify-content-between align-items-center">
        <div class="text-muted">
            Menampilkan 1 hingga 5 dari 25 entri
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Sebelumnya</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Selanjutnya</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- ✅ Modal History (Bootstrap) -->
    <div class="modal fade" id="modalHistory" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-clock-history"></i> Detail History Barang</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-cart-check"></i> Data Request</h6>
                    <div class="mb-3">
                        <p><strong>No Tiket:</strong> <span id="modal-tiket-display">-</span></p>
                        <p><strong>Requester:</strong> <span id="modal-requester-display">-</span></p>
                        <p><strong>Tanggal Request:</strong> <span id="modal-tanggal-request-display">-</span></p>
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Item</th>
                                    <th>Deskripsi</th>
                                    <th>Jumlah Diminta</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="request-table-body">
                                <tr>
                                    <td colspan="5" class="text-center">Pilih tiket untuk melihat detail.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr>
                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-truck"></i> Data Pengiriman</h6>
                    <div class="mb-3">
                        <p><strong>Tanggal Pengiriman:</strong> <span id="modal-tanggal-pengiriman-display">-</span></p>
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-success">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Item</th>
                                    <th>Merk</th>
                                    <th>SN</th>
                                    <th>Tipe</th>
                                    <th>Jumlah Dikirim</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="pengiriman-table-body">
                                <tr>
                                    <td colspan="7" class="text-center">Pilih tiket untuk melihat detail.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Include Komponen Modal Tracking -->
    @include('components.tracking-modal')

@endsection

@push('scripts')
    <script>
        // Pastikan backdrop hilang saat modal ditutup
        const modalElement = document.getElementById('modalHistory');
        if (modalElement) {
            modalElement.addEventListener('hidden.bs.modal', function () {
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                document.body.style.overflow = '';
                document.body.classList.remove('modal-open');
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Highlight menu aktif
            const currentLocation = location.href;
            const menuItems = document.querySelectorAll('.list-group-item');
            const menuLength = menuItems.length;

            for (let i = 0; i < menuLength; i++) {
                if (menuItems[i].href === currentLocation) {
                    menuItems[i].classList.add('active');
                }
            }

            // Set tanggal default untuk filter
            const today = new Date();
            const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

            document.getElementById('dateFrom').valueAsDate = firstDayOfMonth;
            document.getElementById('dateTo').valueAsDate = today;

            // Toggle sidebar on mobile (if needed)
            document.querySelector('.navbar-toggler').addEventListener('click', function () {
                document.querySelector('.sidebar').classList.toggle('show');
            });
        });

        // Load detail history saat modal dibuka
        document.querySelectorAll('.btn-history').forEach(button => {
            button.addEventListener('click', function () {
                const tiket = this.dataset.tiket;

                // Reset isi modal
                document.getElementById('modal-tiket-display').textContent = '-';
                document.getElementById('modal-requester-display').textContent = '-';
                document.getElementById('modal-tanggal-request-display').textContent = '-';
                document.getElementById('modal-tanggal-pengiriman-display').textContent = '-';

                // Reset tabel
                document.getElementById('request-table-body').innerHTML = '<tr><td colspan="5" class="text-center">Memuat data...</td></tr>';
                document.getElementById('pengiriman-table-body').innerHTML = '<tr><td colspan="7" class="text-center">Memuat data...</td></tr>';

                // Ambil data dari API
                fetch(`/kepalagudang/history/${tiket}/api`)
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(errData => {
                                throw new Error(errData.error || 'Gagal memuat data');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Isi data request
                        document.getElementById('modal-tiket-display').textContent = data.permintaan.tiket;
                        document.getElementById('modal-requester-display').textContent = data.permintaan.user?.name || 'User';
                        document.getElementById('modal-tanggal-request-display').textContent = new Date(data.permintaan.tanggal_permintaan)
                            .toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });

                        // Isi tabel request
                        const requestTableBody = document.getElementById('request-table-body');
                        requestTableBody.innerHTML = '';
                        if (data.permintaan.details && data.permintaan.details.length > 0) {
                            data.permintaan.details.forEach((item, index) => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td>${index + 1}</td>
                                    <td>${item.nama_item}</td>
                                    <td>${item.deskripsi || '-'}</td>
                                    <td>${item.jumlah}</td>
                                    <td>${item.keterangan || '-'}</td>
                                `;
                                requestTableBody.appendChild(tr);
                            });
                        } else {
                            requestTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Tidak ada data request.</td></tr>';
                        }

                        // Isi data pengiriman
                        if (data.pengiriman) {
                            document.getElementById('modal-tanggal-pengiriman-display').textContent = new Date(data.pengiriman.tanggal_transaksi)
                                .toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });

                            const pengirimanTableBody = document.getElementById('pengiriman-table-body');
                            pengirimanTableBody.innerHTML = '';
                            if (data.pengiriman.details && data.pengiriman.details.length > 0) {
                                data.pengiriman.details.forEach((item, index) => {
                                    const tr = document.createElement('tr');
                                    tr.innerHTML = `
                                        <td>${index + 1}</td>
                                        <td>${item.nama_item}</td>
                                        <td>${item.merk || '-'}</td>
                                        <td>${item.sn || '-'}</td>
                                        <td>${item.tipe || '-'}</td>
                                        <td>${item.jumlah}</td>
                                        <td>${item.keterangan || '-'}</td>
                                    `;
                                    pengirimanTableBody.appendChild(tr);
                                });
                            } else {
                                pengirimanTableBody.innerHTML = '<tr><td colspan="7" class="text-center">Tidak ada data pengiriman.</td></tr>';
                            }
                        } else {
                            document.getElementById('modal-tanggal-pengiriman-display').textContent = '-';
                            document.getElementById('pengiriman-table-body').innerHTML = '<tr><td colspan="7" class="text-center">Belum ada pengiriman.</td></tr>';
                        }

                        // Buka modal
                        const modal = new bootstrap.Modal(document.getElementById('modalHistory'));
                        modal.show();
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        alert('⚠️ ' + err.message);
                    });
            });
        });
    </script>
@endpush