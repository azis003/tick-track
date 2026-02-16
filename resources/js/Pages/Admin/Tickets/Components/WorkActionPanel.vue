<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { 
    PlayCircle, 
    // PauseCircle, 
    CheckCircle, 
    SquareArrowLeft, 
    AlertTriangle,
    ExternalLink,
    FileText,
    X,
    Upload
} from 'lucide-vue-next'

/**
 * WorkActionPanel Component
 * Panel aksi untuk Teknisi saat mengerjakan tiket:
 * - Set Pending (User/External)
 * - Resume from Pending
 * - Request Approval
 * - Resolve Ticket
 */
const props = defineProps({
    ticket: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close'])

// Modal states
const showPendingModal = ref(false)
const showResolveModal = ref(false)
const showApprovalModal = ref(false)

// Forms
const pendingForm = useForm({
    type: 'user',
    reason: '',
    attachments: []
})

const resolveForm = useForm({
    resolution: '',
    evidence: []
})

const approvalForm = useForm({
    request_type: 'purchase',
    request_reason: '',
    estimated_cost: null
})

// Computed
const isInProgress = computed(() => props.ticket.status === 'in_progress')
const isPending = computed(() => ['pending_user', 'pending_external'].includes(props.ticket.status))
const isWaitingApproval = computed(() => props.ticket.status === 'waiting_approval')

const canSetPending = computed(() => isInProgress.value)
const canResume = computed(() => isPending.value)
const canResolve = computed(() => ['in_progress', 'pending_user', 'pending_external'].includes(props.ticket.status))
const canRequestApproval = computed(() => isInProgress.value)

// Methods
const submitPending = () => {
    pendingForm.post(`/admin/tickets/${props.ticket.id}/pending`, {
        preserveScroll: true,
        onSuccess: () => {
            pendingForm.reset()
            showPendingModal.value = false
        }
    })
}

const resumeTicket = () => {
    Swal.fire({
        title: 'Lanjutkan Pengerjaan?',
        text: 'Anda akan mulai mengerjakan kembali tiket ini.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb', // blue-600
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            useForm({ notes: 'Dilanjutkan' }).post(`/admin/tickets/${props.ticket.id}/resume`, {
                preserveScroll: true
            })
        }
    })
}

const submitResolve = () => {
    resolveForm.post(`/admin/tickets/${props.ticket.id}/resolve`, {
        preserveScroll: true,
        onSuccess: () => {
            resolveForm.reset()
            showResolveModal.value = false
        }
    })
}

const submitApproval = () => {
    approvalForm.post(`/admin/tickets/${props.ticket.id}/request-approval`, {
        preserveScroll: true,
        onSuccess: () => {
            approvalForm.reset()
            showApprovalModal.value = false
        }
    })
}

const handleFileUpload = (event) => {
    const files = Array.from(event.target.files)
    resolveForm.evidence = files
}

const handlePendingFileUpload = (event) => {
    const files = Array.from(event.target.files)
    pendingForm.attachments = [...(pendingForm.attachments || []), ...files]
}
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
            <div class="flex items-center gap-2">
                <!-- <div class="p-1.5 bg-blue-100 rounded-lg">
                    <PlayCircle class="w-4 h-4 text-blue-600" />
                </div> -->
                <h3 class="font-medium text-gray-900">Panel Pengerjaan</h3>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <!-- Status: In Progress -->
            <div v-if="isInProgress" class="space-y-3">
                <p class="text-sm text-gray-600">
                    Tiket sedang dalam pengerjaan. Pilih aksi di bawah:
                </p>

                <!-- Set Pending Button -->
                <button
                    @click="showPendingModal = true"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-orange-300 text-orange-700 rounded-lg hover:bg-orange-50 transition-colors"
                >
                    <!-- <PauseCircle class="w-5 h-5" /> -->
                    <span>Set Pending</span>
                </button>

                <!-- Request Approval Button -->
                <button
                    @click="showApprovalModal = true"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-purple-300 text-purple-700 rounded-lg hover:bg-purple-50 transition-colors"
                >
                    <!-- <AlertTriangle class="w-5 h-5" /> -->
                    <span>Minta Approval</span>
                </button>

                <!-- Resolve Button -->
                <button
                    @click="showResolveModal = true"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-green-300 text-green-700 rounded-lg hover:bg-purple-50 transition-colors"
                >
                    <!-- <CheckCircle class="w-5 h-5" /> -->
                    <span>Selesaikan Tiket</span>
                </button>
            </div>

            <!-- Status: Pending -->
            <div v-if="isPending" class="space-y-3">
                <div class="p-4 bg-orange-50 rounded-lg border border-orange-100">
                    <div class="flex items-start gap-3">
                        <Clock class="w-5 h-5 text-orange-500 mt-0.5" />
                        <div>
                            <p class="font-medium text-orange-800">
                                {{ ticket.status === 'pending_user' ? 'Menunggu User' : 'Menunggu Vendor' }}
                            </p>
                            <p class="text-sm text-orange-700 mt-1" v-if="ticket.pending_reason">
                                {{ ticket.pending_reason }}
                            </p>
                        </div>
                    </div>
                </div>

                <button
                    @click="resumeTicket"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <PlayCircle class="w-5 h-5" />
                    <span>Lanjutkan Pengerjaan</span>
                </button>

                <!-- Allow resolve from pending too -->
                <button
                    @click="showResolveModal = true"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-green-300 text-green-700 rounded-lg hover:bg-green-50 transition-colors"
                >
                    <!-- <CheckCircle class="w-5 h-5" /> -->
                    <span>Selesaikan Tiket</span>
                </button>
            </div>

            <!-- Status: Waiting Approval -->
            <div v-if="isWaitingApproval" class="space-y-3">
                <div class="p-4 bg-pink-50 rounded-lg border border-pink-100">
                    <div class="flex items-start gap-3">
                        <AlertTriangle class="w-5 h-5 text-pink-500 mt-0.5" />
                        <div>
                            <p class="font-medium text-pink-800">Menunggu Approval Manager</p>
                            <p class="text-sm text-pink-700 mt-1">
                                Tiket akan dilanjutkan setelah Manager memberikan keputusan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showPendingModal" class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="fixed inset-0 bg-black/50" @click="showPendingModal = false"></div>
                        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md transform transition-all">
                            <!-- Header -->
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <!-- <div class="p-1.5 bg-orange-100 rounded-lg">
                                        <PauseCircle class="w-4 h-4 text-orange-600" />
                                    </div> -->
                                    <h3 class="text-lg font-semibold text-gray-900">Set Pending</h3>
                                </div>
                                <button @click="showPendingModal = false" class="p-1 hover:bg-gray-100 rounded-lg">
                                    <X class="w-5 h-5 text-gray-500" />
                                </button>
                            </div>

                            <!-- Body -->
                            <form @submit.prevent="submitPending" class="p-6 space-y-4">
                                <!-- Pending Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Pending *</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button
                                            type="button"
                                            @click="pendingForm.type = 'user'"
                                            :class="[
                                                'p-3 rounded-lg border-2 text-left transition-all',
                                                pendingForm.type === 'user'
                                                    ? 'border-orange-500 bg-orange-50'
                                                    : 'border-gray-200 hover:border-gray-300'
                                            ]"
                                        >
                                            <SquareArrowLeft class="w-5 h-5 text-orange-500 mb-1" />
                                            <p class="font-medium text-sm">Kembalikan ke Pelapor</p>
                                            <!-- <p class="text-xs text-gray-500">Menunggu info dari user</p> -->
                                        </button>
                                        <button
                                            type="button"
                                            @click="pendingForm.type = 'external'"
                                            :class="[
                                                'p-3 rounded-lg border-2 text-left transition-all',
                                                pendingForm.type === 'external'
                                                    ? 'border-orange-500 bg-orange-50'
                                                    : 'border-gray-200 hover:border-gray-300'
                                            ]"
                                        >
                                            <ExternalLink class="w-5 h-5 text-orange-500 mb-1" />
                                            <p class="font-medium text-sm">Menunggu Pihak Eksternal</p>
                                            <!-- <p class="text-xs text-gray-500">Menunggu pihak eksternal</p> -->
                                        </button>
                                    </div>
                                </div>

                                <!-- Reason -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan *</label>
                                    <textarea
                                        v-model="pendingForm.reason"
                                        rows="5"
                                        placeholder="Jelaskan alasan pending..."
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 resize-y"
                                        required
                                    ></textarea>
                                    <p v-if="pendingForm.errors.reason" class="mt-1 text-sm text-red-600">
                                        {{ pendingForm.errors.reason }}
                                    </p>
                                </div>

                                <!-- File Pendukung -->
                                <div>
                                    <p class="text-sm font-semibold text-gray-700 mb-2">File Pendukung:</p>
                                    <label class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:border-orange-400 hover:text-orange-600 cursor-pointer transition-colors bg-white">
                                        <Upload class="w-4 h-4" />
                                        <span>Pilih File</span>
                                        <input
                                            type="file"
                                            multiple
                                            @change="handlePendingFileUpload"
                                            class="hidden"
                                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.zip"
                                        />
                                    </label>
                                    <p class="mt-2 text-xs text-orange-500">
                                        Tipe file yang dapat diupload: png, jpg, jpeg, pdf, docx, zip — maksimal 8MB
                                    </p>

                                    <!-- Attached files preview -->
                                    <div v-if="pendingForm.attachments && pendingForm.attachments.length > 0" class="mt-3 flex flex-wrap gap-2">
                                        <div 
                                            v-for="(file, index) in pendingForm.attachments" 
                                            :key="index" 
                                            class="flex items-center gap-2 px-3 py-1.5 bg-orange-50 border border-orange-100 rounded-lg text-xs text-orange-700"
                                        >
                                            <FileText class="w-3 h-3" />
                                            <span>{{ file.name }}</span>
                                            <button 
                                                type="button"
                                                @click="pendingForm.attachments.splice(index, 1)"
                                                class="p-0.5 hover:text-red-500 transition-colors"
                                            >
                                                <X class="w-3 h-3" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-3 pt-2">
                                    <button
                                        type="button"
                                        @click="showPendingModal = false"
                                        class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="pendingForm.processing || !pendingForm.reason"
                                        class="flex-1 px-4 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {{ pendingForm.processing ? 'Memproses...' : 'Set Pending' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Resolve Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showResolveModal" class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="fixed inset-0 bg-black/50" @click="showResolveModal = false"></div>
                        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg transform transition-all">
                            <!-- Header -->
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <!-- <div class="p-1.5 bg-green-100 rounded-lg">
                                        <CheckCircle class="w-4 h-4 text-green-600" />
                                    </div> -->
                                    <h3 class="text-lg font-semibold text-gray-900">Selesaikan Tiket</h3>
                                </div>
                                <button @click="showResolveModal = false" class="p-1 hover:bg-gray-100 rounded-lg">
                                    <X class="w-5 h-5 text-gray-500" />
                                </button>
                            </div>

                            <!-- Body -->
                            <form @submit.prevent="submitResolve" class="p-6 space-y-4">
                                <!-- Resolution -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Solusi/Penyelesaian *</label>
                                    <textarea
                                        v-model="resolveForm.resolution"
                                        rows="5"
                                        placeholder="Jelaskan langkah-langkah penyelesaian yang dilakukan (min. 20 karakter)"
                                        class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500"
                                        required
                                    ></textarea>
                                    <p v-if="resolveForm.errors.resolution" class="mt-1 text-sm text-red-600">
                                        {{ resolveForm.errors.resolution }}
                                    </p>
                                </div>

                                <!-- Evidence Upload -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Bukti Penyelesaian (opsional)
                                    </label>
                                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 text-center hover:border-green-300 transition-colors">
                                        <input
                                            type="file"
                                            multiple
                                            @change="handleFileUpload"
                                            class="hidden"
                                            id="evidence-upload"
                                            accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx"
                                        />
                                        <label for="evidence-upload" class="cursor-pointer">
                                            <Upload class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                                            <p class="text-sm text-gray-600">Klik untuk upload file bukti</p>
                                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF, DOC (max 10MB)</p>
                                        </label>
                                    </div>
                                    <div v-if="resolveForm.evidence.length > 0" class="mt-2 space-y-1">
                                        <div v-for="(file, index) in resolveForm.evidence" :key="index" class="flex items-center gap-2 text-sm text-gray-600">
                                            <FileText class="w-4 h-4" />
                                            <span>{{ file.name }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-3 pt-2">
                                    <button
                                        type="button"
                                        @click="showResolveModal = false"
                                        class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="resolveForm.processing || !resolveForm.resolution"
                                        class="flex-1 px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {{ resolveForm.processing ? 'Memproses...' : 'Selesaikan' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Approval Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showApprovalModal" class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="fixed inset-0 bg-black/50" @click="showApprovalModal = false"></div>
                        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md transform transition-all">
                            <!-- Header -->
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <!-- <div class="p-1.5 bg-purple-100 rounded-lg">
                                        <AlertTriangle class="w-4 h-4 text-purple-600" />
                                    </div> -->
                                    <h3 class="text-lg font-semibold text-gray-900">Minta Approval</h3>
                                </div>
                                <button @click="showApprovalModal = false" class="p-1 hover:bg-gray-100 rounded-lg">
                                    <X class="w-5 h-5 text-gray-500" />
                                </button>
                            </div>

                            <!-- Body -->
                            <form @submit.prevent="submitApproval" class="p-6 space-y-4">
                                <!-- Request Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Permintaan *</label>
                                    <select
                                        v-model="approvalForm.request_type"
                                        class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                                    >
                                        <option value="purchase">Pembelian Barang/Jasa</option>
                                        <option value="vendor">Penggunaan Vendor</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                </div>

                                <!-- Estimated Cost -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Estimasi Biaya (opsional)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                                        <input
                                            type="number"
                                            v-model="approvalForm.estimated_cost"
                                            placeholder="0"
                                            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                                        />
                                    </div>
                                </div>

                                <!-- Reason -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Permintaan *</label>
                                    <textarea
                                        v-model="approvalForm.request_reason"
                                        rows="4"
                                        placeholder="Jelaskan mengapa perlu approval..."
                                        class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                                        required
                                    ></textarea>
                                    <p v-if="approvalForm.errors.request_reason" class="mt-1 text-sm text-red-600">
                                        {{ approvalForm.errors.request_reason }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-3 pt-2">
                                    <button
                                        type="button"
                                        @click="showApprovalModal = false"
                                        class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="approvalForm.processing || !approvalForm.request_reason"
                                        class="flex-1 px-4 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {{ approvalForm.processing ? 'Memproses...' : 'Kirim Permintaan' }}
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
