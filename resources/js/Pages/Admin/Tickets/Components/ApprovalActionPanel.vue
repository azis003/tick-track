<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import {
    Shield,
    CheckCircle,
    XCircle,
    FileText,
    DollarSign,
    User,
    Calendar,
    AlertTriangle
} from 'lucide-vue-next'

/**
 * ApprovalActionPanel Component
 * Panel aksi untuk Manager TI: Approve atau Reject permintaan approval
 */
const props = defineProps({
    ticket: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close'])

// Get the latest pending approval
const latestApproval = computed(() => {
    if (!props.ticket.approvals || props.ticket.approvals.length === 0) return null
    return props.ticket.approvals.find(a => a.status === 'pending') || props.ticket.approvals[0]
})

// Request type labels
const requestTypeLabels = {
    purchase: 'Pembelian Barang/Jasa',
    vendor: 'Penggunaan Vendor',
    other: 'Lainnya'
}

// Forms
const approveForm = useForm({
    decision_notes: ''
})

const rejectForm = useForm({
    decision_notes: ''
})

// Modal state
const showRejectModal = ref(false)

// Format currency
const formatCurrency = (amount) => {
    if (!amount) return '-'
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount)
}

// Format date
const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

// Approve
const submitApprove = () => {
    Swal.fire({
        title: 'Setujui Permintaan?',
        text: 'Anda akan menyetujui permintaan ini. Tiket akan dilanjutkan ke proses pengerjaan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            approveForm.post(`/admin/tickets/${props.ticket.id}/approve`, {
                preserveScroll: true,
                onSuccess: () => {
                    approveForm.reset()
                }
            })
        }
    })
}

// Reject
const submitReject = () => {
    if (!rejectForm.decision_notes || rejectForm.decision_notes.length < 10) {
        Swal.fire({
            title: 'Alasan Diperlukan',
            text: 'Mohon masukkan alasan penolakan minimal 10 karakter.',
            icon: 'warning',
            confirmButtonColor: '#ef4444',
        })
        return
    }

    rejectForm.post(`/admin/tickets/${props.ticket.id}/reject`, {
        preserveScroll: true,
        onSuccess: () => {
            rejectForm.reset()
            showRejectModal.value = false
        }
    })
}
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-pink-50 to-purple-50">
            <div class="flex items-center gap-2">
                <div class="p-1.5 bg-pink-100 rounded-lg">
                    <Shield class="w-4 h-4 text-pink-600" />
                </div>
                <h3 class="font-medium text-gray-900">Panel Approval</h3>
            </div>
        </div>

        <div class="p-6 space-y-5">
            <!-- Approval Detail Card -->
            <div v-if="latestApproval" class="space-y-4">
                <!-- Request Info -->
                <div class="p-4 bg-purple-50/50 rounded-xl border border-purple-100">
                    <h4 class="text-xs font-bold text-purple-400 uppercase tracking-wider mb-3">Detail Permintaan</h4>

                    <div class="space-y-3">
                        <!-- Request Type -->
                        <div class="flex items-start gap-3">
                            <FileText class="w-4 h-4 text-purple-500 mt-0.5 shrink-0" />
                            <div>
                                <p class="text-xs text-gray-500">Tipe Permintaan</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ requestTypeLabels[latestApproval.request_type] || latestApproval.request_type }}
                                </p>
                            </div>
                        </div>

                        <!-- Estimated Cost -->
                        <div v-if="latestApproval.estimated_cost" class="flex items-start gap-3">
                            <DollarSign class="w-4 h-4 text-green-500 mt-0.5 shrink-0" />
                            <div>
                                <p class="text-xs text-gray-500">Estimasi Biaya</p>
                                <p class="text-sm font-bold text-green-700">
                                    {{ formatCurrency(latestApproval.estimated_cost) }}
                                </p>
                            </div>
                        </div>

                        <!-- Requested By -->
                        <div class="flex items-start gap-3">
                            <User class="w-4 h-4 text-blue-500 mt-0.5 shrink-0" />
                            <div>
                                <p class="text-xs text-gray-500">Diajukan Oleh</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ latestApproval.requested_by?.name || ticket.assigned_to?.name || '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Requested At -->
                        <div class="flex items-start gap-3">
                            <Calendar class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                            <div>
                                <p class="text-xs text-gray-500">Waktu Pengajuan</p>
                                <p class="text-sm text-gray-700">
                                    {{ formatDate(latestApproval.requested_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reason -->
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Alasan Permintaan</h4>
                    <div class="p-3 bg-gray-50 rounded-lg text-sm text-gray-700 whitespace-pre-wrap leading-relaxed border border-gray-100">
                        {{ latestApproval.request_reason || '-' }}
                    </div>
                </div>

                <!-- Catatan Approval (opsional) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan Keputusan (opsional)
                    </label>
                    <textarea
                        v-model="approveForm.decision_notes"
                        rows="2"
                        placeholder="Tambahkan catatan jika diperlukan..."
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500"
                    ></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3 pt-2">
                    <!-- Approve Button -->
                    <button
                        @click="submitApprove"
                        :disabled="approveForm.processing"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                    >
                        <CheckCircle class="w-5 h-5" />
                        <span>{{ approveForm.processing ? 'Memproses...' : 'Setujui Permintaan' }}</span>
                    </button>

                    <!-- Reject Button -->
                    <button
                        @click="showRejectModal = true"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border-2 border-red-200 text-red-600 rounded-lg hover:bg-red-50 hover:border-red-300 transition-colors"
                    >
                        <XCircle class="w-5 h-5" />
                        <span>Tolak Permintaan</span>
                    </button>
                </div>
            </div>

            <!-- No approval data -->
            <div v-else class="text-center py-4">
                <AlertTriangle class="w-8 h-8 text-gray-300 mx-auto mb-2" />
                <p class="text-sm text-gray-500">Data approval tidak ditemukan.</p>
            </div>
        </div>

        <!-- Reject Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="fixed inset-0 bg-black/50" @click="showRejectModal = false"></div>
                        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md transform transition-all">
                            <!-- Header -->
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 bg-red-100 rounded-lg">
                                        <XCircle class="w-4 h-4 text-red-600" />
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900">Tolak Permintaan</h3>
                                </div>
                                <button @click="showRejectModal = false" class="p-1 hover:bg-gray-100 rounded-lg">
                                    <XCircle class="w-5 h-5 text-gray-500" />
                                </button>
                            </div>

                            <!-- Body -->
                            <form @submit.prevent="submitReject" class="p-6 space-y-4">
                                <p class="text-sm text-gray-600">
                                    Permintaan akan ditolak dan tiket akan dikembalikan ke status "Dalam Proses" agar teknisi dapat melanjutkan dengan cara lain.
                                </p>

                                <!-- Reason -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan *</label>
                                    <textarea
                                        v-model="rejectForm.decision_notes"
                                        rows="4"
                                        placeholder="Jelaskan alasan penolakan permintaan ini..."
                                        class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                                        required
                                    ></textarea>
                                    <p v-if="rejectForm.errors.decision_notes" class="mt-1 text-sm text-red-600">
                                        {{ rejectForm.errors.decision_notes }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-3 pt-2">
                                    <button
                                        type="button"
                                        @click="showRejectModal = false"
                                        class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="rejectForm.processing || !rejectForm.decision_notes"
                                        class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {{ rejectForm.processing ? 'Memproses...' : 'Tolak Permintaan' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
