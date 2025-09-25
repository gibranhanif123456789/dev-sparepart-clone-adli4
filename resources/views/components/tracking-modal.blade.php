<!-- resources/views/components/tracking-modal.blade.php -->

<!-- Modal Tracking Status -->
<div id="statusModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen">
        <!-- Backdrop -->
        <div class="bg-black bg-opacity-50 absolute inset-0" onclick="closeStatusModal()"></div>

        <!-- Modal Content -->
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 z-10">
            <div class="modal-header bg-blue-600 text-white p-4 rounded-t-lg flex justify-between items-center">
                <h5 class="text-lg font-semibold">Detail Progres Approval</h5>
                <button onclick="closeStatusModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Body: Status Akan Diisi oleh JS -->
            <div class="p-6" id="statusModalBody">
                <p class="text-center text-gray-500">Memuat status...</p>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end">
                <button onclick="closeStatusModal()" class="btn btn-secondary bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-md text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Script Modal Tracking -->
<script>
// Fungsi utama: tampilkan modal dengan data dari API
function showStatusDetailModal(tiket, userRole) {
    const modal = document.getElementById('statusModal');
    const modalBody = document.getElementById('statusModalBody');

    // Tampilkan loading
    modalBody.innerHTML = '<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat status...</p>';
    modal.classList.remove('hidden'); // Tampilkan modal

    // Fetch data dari API
    fetch(`/requestbarang/api/permintaan/${tiket}/status`)
        .then(response => {
            if (!response.ok) throw new Error('Tiket tidak ditemukan');
            return response.json();
        })
        .then(data => {
            let html = '<ul class="space-y-2">';

            // Daftar role yang ditampilkan
            const roles = [
                { key: 'ro', label: 'Kepala RO' },
                { key: 'gudang', label: 'Kepala Gudang' },
                { key: 'admin', label: 'Admin' },
                { key: 'super_admin', label: 'Super Admin' }
            ];

            roles.forEach(r => {
                let badgeClass = 'bg-gray-100 text-gray-800'; // default
                let statusText = formatStatus(data[r.key]);

                if (data[r.key] === 'approved') {
                    badgeClass = 'bg-green-100 text-green-800';
                } else if (data[r.key] === 'rejected') {
                    badgeClass = 'bg-red-100 text-red-800';
                } else if (data[r.key] === 'on progres') {
                    badgeClass = 'bg-yellow-100 text-yellow-800';
                } else if (data[r.key] === 'close') {
                    badgeClass = 'bg-gray-300 text-gray-800';
                }

                html += `
                    <li class="flex justify-between items-center p-3 border border-gray-200 rounded">
                        <span class="font-medium">${r.label}</span>
                        <span class="px-3 py-1 rounded-full text-xs font-medium ${badgeClass}">
                            ${statusText}
                        </span>
                    </li>`;
            });

            html += '</ul>';
            // 🔥 Tambahkan Status Barang
if (data.status_barang) {
    let barangBadgeClass = 'bg-gray-100 text-gray-800';
    const barangStatus = data.status_barang;

    if (barangStatus === 'on_delivery') {
        barangBadgeClass = 'bg-blue-100 text-blue-800';
    } else if (barangStatus === 'diterima') {
        barangBadgeClass = 'bg-green-100 text-green-800';
    } else if (barangStatus === 'pending') {
        barangBadgeClass = 'bg-yellow-100 text-yellow-800';
    } else if (barangStatus === 'diproses') {
        barangBadgeClass = 'bg-orange-100 text-orange-800';
    }

    html += `
        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex justify-between items-center">
                <span class="font-medium">🚚 Status Barang</span>
                <span class="px-3 py-1 rounded-full text-xs font-medium ${barangBadgeClass}">
                    ${formatBarangStatus(barangStatus)}
                </span>
            </div>
        </div>`;
}

            // Tambahkan catatan jika ada
            if (data.catatan) {
                html += `<p class="mt-4 text-sm"><strong>Catatan:</strong> ${data.catatan}</p>`;
            }

            modalBody.innerHTML = html;
        })
        .catch(err => {
            modalBody.innerHTML = `<p class="text-red-600">${err.message}</p>`;
        });
}

// Fungsi: tutup modal
function closeStatusModal() {
    const modal = document.getElementById('statusModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Helper: format status
function formatStatus(status) {
    const map = {
        pending: 'Pending',
        'on progres': 'On Progress',
        approved: 'Disetujui',
        rejected: 'Ditolak',
        close: 'Selesai'
    };
    return map[status] || status;
}

function formatBarangStatus(status) {
    const map = {
        pending: 'Pending',
        'on_delivery': 'On Delivery',
        diterima: 'Diterima',
        diproses: 'Diproses',
        dikirim: 'Dikirim'
    };
    return map[status] || status.charAt(0).toUpperCase() + status.slice(1);
}
</script>