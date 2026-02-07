<script setup>
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import StatusBadge from './StatusBadge.vue'
import PriorityBadge from './PriorityBadge.vue'
import { 
    Eye, 
    ChevronLeft, 
    ChevronRight, 
    Play, 
    RotateCcw, 
    Wrench,
    X
} from 'lucide-vue-next'

/**
 * TechnicianTicketTable Component
 * Tabel tiket khusus untuk Teknisi dengan aksi kontekstual:
 * - Status assigned: Tombol Mulai Kerjakan + Kembalikan
 * - Status in_progress/pending: Tombol Kerjakan (ke detail)
 */
const props = defineProps({
    tickets: {
        type: Object,
        required: true
    },
    statuses: {
        type: Object,
        default: () => ({})
    }
})

const showReturnModal = ref(false)
const selectedTicket = ref(null)
const returnForm = useForm({
    reason: ''
})

const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

// Accept and start working on ticket
const acceptTicket = (ticketId) => {
    if (confirm('Mulai kerjakan tiket ini?')) {
        router.post(`/admin/tickets/${ticketId}/accept`, {}, {
            preserveScroll: true
        })
    }
}

// Open return modal
const openReturnModal = (ticket) => {
    selectedTicket.value = ticket
    returnForm.reset()
    showReturnModal.value = true
}

// Submit return
const submitReturn = () => {
    if (!selectedTicket.value) return
    
    returnForm.post(`/admin/tickets/${selectedTicket.value.id}/return`, {
        preserveScroll: true,
        onSuccess: () => {
            showReturnModal.value = false
            selectedTicket.value = null
            returnForm.reset()
        }
    })
}

// Check if ticket needs action buttons based on status
const needsAcceptAction = (status) => status === 'assigned'
const isWorkingStatus = (status) => ['in_progress', 'pending_user', 'pending_external', 'waiting_approval'].includes(status)
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            No. Tiket
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Judul
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Prioritas
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pelapor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Waktu
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="ticket in tickets.data"
                        :key="ticket.id"
                        class="hover:bg-gray-50 transition-colors"
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <Link
                                :href="`/admin/tickets/${ticket.id}`"
                                class="text-blue-600 hover:text-blue-800 font-mono text-sm font-medium"
                            >
                                {{ ticket.ticket_number }}
                            </Link>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 line-clamp-2 max-w-xs">
                                {{ ticket.title }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <PriorityBadge
                                v-if="ticket.final_priority || ticket.user_priority"
                                :priority="ticket.final_priority || ticket.user_priority"
                            />
                            <span v-else class="text-sm text-gray-400">-</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <StatusBadge
                                :status="ticket.status"
                                :label="statuses[ticket.status] || ticket.status"
                            />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600">
                                {{ ticket.reporter?.name || '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatDate(ticket.created_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <!-- Actions for ASSIGNED status: Mulai + Kembalikan -->
                            <div v-if="needsAcceptAction(ticket.status)" class="flex items-center justify-end gap-2">
                                <button
                                    @click="acceptTicket(ticket.id)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                                >
                                    <Play class="h-4 w-4" />
                                    <span>Mulai</span>
                                </button>
                                <button
                                    @click="openReturnModal(ticket)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-orange-600 hover:text-orange-800 hover:bg-orange-50 rounded-lg transition-colors"
                                >
                                    <RotateCcw class="h-4 w-4" />
                                    <span>Kembalikan</span>
                                </button>
                            </div>

                            <!-- Actions for IN_PROGRESS/PENDING status: Kerjakan -->
                            <div v-else-if="isWorkingStatus(ticket.status)" class="flex items-center justify-end gap-2">
                                <Link
                                    :href="`/admin/tickets/${ticket.id}`"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors"
                                >
                                    <Wrench class="h-4 w-4" />
                                    <span>Kerjakan</span>
                                </Link>
                            </div>

                            <!-- Default: Lihat -->
                            <div v-else>
                                <Link
                                    :href="`/admin/tickets/${ticket.id}`"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors"
                                >
                                    <Eye class="h-4 w-4" />
                                    <span>Lihat</span>
                                </Link>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty state -->
                    <tr v-if="!tickets.data || tickets.data.length === 0">
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <h3 class="text-sm font-medium text-gray-900">Tidak ada tiket ditugaskan</h3>
                                <p class="text-sm text-gray-500 mt-1">Tiket akan tampil di sini setelah ditugaskan kepada Anda</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="tickets.last_page > 1" class="px-6 py-4 bg-gray-50 border-t flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Menampilkan {{ tickets.from }} - {{ tickets.to }} dari {{ tickets.total }} tiket
            </div>
            <div class="flex gap-2">
                <Link
                    v-if="tickets.prev_page_url"
                    :href="tickets.prev_page_url"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm border rounded-lg hover:bg-gray-100 transition-colors"
                    preserve-scroll
                >
                    <ChevronLeft class="h-4 w-4" />
                    <span>Prev</span>
                </Link>
                <Link
                    v-if="tickets.next_page_url"
                    :href="tickets.next_page_url"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm border rounded-lg hover:bg-gray-100 transition-colors"
                    preserve-scroll
                >
                    <span>Next</span>
                    <ChevronRight class="h-4 w-4" />
                </Link>
            </div>
        </div>

        <!-- Return Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showReturnModal" class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="fixed inset-0 bg-black/50" @click="showReturnModal = false"></div>
                        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md transform transition-all">
                            <!-- Header -->
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 bg-orange-100 rounded-lg">
                                        <RotateCcw class="w-4 h-4 text-orange-600" />
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900">Kembalikan Tiket</h3>
                                </div>
                                <button @click="showReturnModal = false" class="p-1 hover:bg-gray-100 rounded-lg">
                                    <X class="w-5 h-5 text-gray-500" />
                                </button>
                            </div>

                            <!-- Body -->
                            <form @submit.prevent="submitReturn" class="p-6 space-y-4">
                                <div class="p-4 bg-orange-50 rounded-lg border border-orange-100">
                                    <p class="text-sm text-orange-800">
                                        ⚠️ Tiket akan dikembalikan ke Helpdesk untuk di-reassign atau dikerjakan oleh orang lain.
                                    </p>
                                </div>

                                <div v-if="selectedTicket">
                                    <p class="text-sm text-gray-600 mb-1">Tiket:</p>
                                    <p class="font-medium text-gray-900">
                                        {{ selectedTicket.ticket_number }} - {{ selectedTicket.title }}
                                    </p>
                                </div>

                                <!-- Reason -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Pengembalian *</label>
                                    <textarea
                                        v-model="returnForm.reason"
                                        rows="3"
                                        placeholder="Jelaskan alasan tidak bisa mengerjakan tiket ini..."
                                        class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                        required
                                    ></textarea>
                                    <p v-if="returnForm.errors.reason" class="mt-1 text-sm text-red-600">
                                        {{ returnForm.errors.reason }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-3 pt-2">
                                    <button
                                        type="button"
                                        @click="showReturnModal = false"
                                        class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="returnForm.processing || !returnForm.reason"
                                        class="flex-1 px-4 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {{ returnForm.processing ? 'Memproses...' : 'Kembalikan' }}
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
