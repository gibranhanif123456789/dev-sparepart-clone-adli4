<!-- Modal Detail Approval Berjenjang -->
<div x-data="{ showStatusDetail: false, status: {}, role: 'user' }"
     x-show="showStatusDetail"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;"
     id="status-detail-modal">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-black bg-opacity-50 absolute inset-0" @click="showStatusDetail = false"></div>
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 z-10">
            <div class="modal-header bg-blue-600 text-white p-4 rounded-t-lg flex justify-between items-center">
                <h5 class="text-lg font-semibold">Detail Progres Approval</h5>
                <button @click="showStatusDetail = false" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="space-y-4 text-sm">
                    <!-- Kepala RO -->
                    <template x-if="['user', 'kepala_ro', 'kepala_gudang', 'admin', 'super_admin'].includes(role)">
                        <div class="flex justify-between items-center p-3 border border-gray-200 rounded">
                            <span class="font-medium">Kepala RO</span>
                            <span :class="{
                                'bg-yellow-100 text-yellow-800': status.ro === 'pending',
                                'bg-green-100 text-green-800': status.ro === 'approved',
                                'bg-red-100 text-red-800': status.ro === 'rejected'
                            }" class="px-3 py-1 rounded-full text-xs font-medium">
                                <template x-if="status.ro === 'pending'">Pending</template>
                                <template x-if="status.ro === 'approved'">Disetujui</template>
                                <template x-if="status.ro === 'rejected'">Ditolak</template>
                            </span>
                        </div>
                    </template>

                    <!-- Kepala Gudang -->
                    <template x-if="['user', 'kepala_ro', 'kepala_gudang', 'admin', 'super_admin'].includes(role)">
                        <div class="flex justify-between items-center p-3 border border-gray-200 rounded">
                            <span class="font-medium">Kepala Gudang</span>
                            <span :class="{
                                'bg-yellow-100 text-yellow-800': status.gudang === 'pending',
                                'bg-green-100 text-green-800': status.gudang === 'approved',
                                'bg-red-100 text-red-800': status.gudang === 'rejected'
                            }" class="px-3 py-1 rounded-full text-xs font-medium">
                                <template x-if="status.gudang === 'pending'">Pending</template>
                                <template x-if="status.gudang === 'approved'">Disetujui</template>
                                <template x-if="status.gudang === 'rejected'">Ditolak</template>
                            </span>
                        </div>
                    </template>

                    <!-- Admin -->
                    <template x-if="['user', 'kepala_ro', 'kepala_gudang', 'admin', 'super_admin'].includes(role)">
                        <div class="flex justify-between items-center p-3 border border-gray-200 rounded">
                            <span class="font-medium">Admin</span>
                            <span :class="{
                                'bg-yellow-100 text-yellow-800': status.admin === 'pending',
                                'bg-green-100 text-green-800': status.admin === 'approved',
                                'bg-red-100 text-red-800': status.admin === 'rejected'
                            }" class="px-3 py-1 rounded-full text-xs font-medium">
                                <template x-if="status.admin === 'pending'">Pending</template>
                                <template x-if="status.admin === 'approved'">Disetujui</template>
                                <template x-if="status.admin === 'rejected'">Ditolak</template>
                            </span>
                        </div>
                    </template>

                    <!-- Super Admin -->
                    <template x-if="['user', 'kepala_ro', 'kepala_gudang', 'admin', 'super_admin'].includes(role)">
                        <div class="flex justify-between items-center p-3 border border-gray-200 rounded">
                            <span class="font-medium">Super Admin</span>
                            <span :class="{
                                'bg-yellow-100 text-yellow-800': status.super_admin === 'pending',
                                'bg-green-100 text-green-800': status.super_admin === 'approved',
                                'bg-red-100 text-red-800': status.super_admin === 'rejected'
                            }" class="px-3 py-1 rounded-full text-xs font-medium">
                                <template x-if="status.super_admin === 'pending'">Pending</template>
                                <template x-if="status.super_admin === 'approved'">Disetujui</template>
                                <template x-if="status.super_admin === 'rejected'">Ditolak</template>
                            </span>
                        </div>
                    </template>

                    <!-- Catatan Jika Ada -->
                    <div class="mt-4 text-sm" x-show="status.catatan">
                        <strong>Catatan Terakhir:</strong>
                        <p x-text="status.catatan" class="text-gray-600 mt-1"></p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end">
                <button @click="showStatusDetail = false" class="btn btn-secondary bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-md text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>