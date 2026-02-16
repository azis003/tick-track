<script setup>
import { Link, usePage, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import StatusBadge from './StatusBadge.vue'
import PriorityBadge from './PriorityBadge.vue'
import Pagination from '@/Shared/Pagination.vue'
import { Eye } from 'lucide-vue-next'

/**
 * TicketTable Component
 * Tabel data tiket dengan pagination
 */
const props = defineProps({
    tickets: {
        type: Object,
        required: true
    },
    statuses: {
        type: Object,
        default: () => ({})
    },
    showReporter: {
        type: Boolean,
        default: true
    },
    showAssignee: {
        type: Boolean,
        default: false
    },
    highlightActionRequired: {
        type: Boolean,
        default: false
    },
    highlightTriageRequired: {
        type: Boolean,
        default: false
    },
    showWorkAction: {
        type: Boolean,
        default: false
    },
    from: {
        type: String,
        default: 'my-tickets'
    }
})

const page = usePage()
const auth = computed(() => page.props.auth)

// Modal state for confirmation (if needed by parent)
const showConfirmModal = ref(false)
const selectedTicket = ref(null)
const confirmForm = useForm({
    reason: ''
})

const submitClose = () => {
    // Implement or emit to parent
}

const submitReopen = () => {
    // Implement or emit to parent
}

// Check if user can triage
const canTriage = computed(() => {
    const permissions = auth.value?.permissions || {}
    return permissions['tickets.triage'] === true
})

// Check if ticket needs verification (status = new or reopened)
const needsVerification = (ticket) => {
    return ['new', 'reopened'].includes(ticket.status)
}

// Check if ticket needs action from reporter (pending_user)
const needsReporterAction = (ticket) => {
    return ticket.status === 'pending_user'
}

// Check if ticket can be worked on (staff's assigned tickets)
const canWork = (ticket) => {
    const workStatuses = ['assigned', 'in_progress', 'pending_external', 'waiting_approval']
    return workStatuses.includes(ticket.status)
}

// Check if ticket needs confirmation from reporter (resolved status)
const needsConfirmation = (ticket) => {
    return ticket.status === 'resolved'
}

// Check if ticket needs acceptance from technician (assigned status)
const needsAcceptance = (ticket) => {
    return ticket.status === 'assigned'
}

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
                            Kategori
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Prioritas
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th v-if="showReporter" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pelapor
                        </th>
                        <th v-if="showAssignee" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ditugaskan
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
                        :class="[
                            'hover:bg-gray-50 transition-colors',
                            // Pegawai: pending_user (orange) - needs response
                            highlightActionRequired && needsReporterAction(ticket) 
                                ? 'bg-orange-50 animate-pulse-slow' 
                                : '',
                            // Pegawai: resolved (green) - needs confirmation
                            highlightActionRequired && needsConfirmation(ticket) 
                                ? 'bg-green-50 animate-pulse-green' 
                                : '',
                            // Helpdesk: new/reopened (purple) - needs triage
                            highlightTriageRequired && needsVerification(ticket) 
                                ? 'bg-purple-50 animate-pulse-purple' 
                                : '',
                            // Teknisi: assigned (blue) - needs acceptance
                            showWorkAction && needsAcceptance(ticket) 
                                ? 'bg-blue-50 animate-pulse-blue' 
                                : ''
                        ]"
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600">
                                {{ ticket.ticket_number }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 line-clamp-2 max-w-xs">
                                {{ ticket.title }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600">
                                {{ ticket.category?.name || '-' }}
                            </span>
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
                        <td v-if="showReporter" class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600">
                                {{ ticket.reporter?.name || '-' }}
                            </span>
                        </td>
                        <td v-if="showAssignee" class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600">
                                {{ ticket.assigned_to?.name || '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatDate(ticket.created_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Link
                                    :href="`/tickets/${ticket.id}?from=${from}`"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors font-medium"
                                >
                                    <Eye class="h-4 w-4" />
                                    <span>Lihat</span>
                                </Link>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty state -->
                    <tr v-if="!tickets.data || tickets.data.length === 0">
                        <td :colspan="showAssignee ? 9 : (showReporter ? 8 : 7)" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <h3 class="text-sm font-medium text-gray-900">Tidak ada tiket ditemukan</h3>
                                <p class="text-sm text-gray-500 mt-1">Tiket akan tampil di sini setelah dibuat</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="tickets.data?.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <p class="text-sm text-gray-600">
                    Menampilkan
                    <span class="font-semibold">{{ tickets.from }}</span>
                    -
                    <span class="font-semibold">{{ tickets.to }}</span>
                    dari
                    <span class="font-semibold">{{ tickets.total }}</span>
                    data
                </p>
                <Pagination :links="tickets.links" />
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showConfirmModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showConfirmModal = false"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200 text-left">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <!-- <div class="p-2 bg-green-100 rounded-lg">
                            <CheckCircle class="w-6 h-6 text-green-600" />
                        </div> -->
                        <h3 class="text-xl font-bold text-gray-900">Konfirmasi Penyelesaian Teknisi</h3>
                    </div>
                    <button @click="showConfirmModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <X class="w-6 h-6" />
                    </button>
                </div>

                <div class="mb-6">
                    <p class="text-gray-600 mb-4">
                        Apakah kendala pada tiket <span class="font-mono font-bold text-blue-600">{{ selectedTicket?.ticket_number }}</span> sudah teratasi dengan baik?
                    </p>
                    
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 mb-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Solusi dari Teknisi:</h4>
                        <p class="text-gray-700 italic">"{{ selectedTicket?.resolution || 'Tidak ada catatan solusi' }}"</p>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700">
                            Catatan Tambahan (Opsional, wajib diisi jika membuka kembali)
                        </label>
                        <textarea
                            v-model="confirmForm.reason"
                            rows="3"
                            class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all text-sm"
                            placeholder="Tuliskan alasan jika masalah belum selesai..."
                        ></textarea>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button
                        @click="submitClose"
                        :disabled="confirmForm.processing"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all shadow-lg shadow-green-200 disabled:opacity-50"
                    >
                        <CheckCircle class="w-5 h-5" />
                        Ya, Selesai
                    </button>
                    <button
                        @click="submitReopen"
                        :disabled="confirmForm.processing"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-red-600 border-2 border-red-100 font-bold rounded-xl hover:bg-red-50 hover:border-red-200 transition-all disabled:opacity-50"
                    >
                        <X class="w-5 h-5" />
                        Re-Open
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Slow pulse animation for rows needing action */
@keyframes pulse-slow {
    0%, 100% {
        background-color: rgb(255 247 237); /* orange-50 */
    }
    50% {
        background-color: rgb(254 215 170); /* orange-200 */
    }
}

.animate-pulse-slow {
    animation: pulse-slow 2s ease-in-out infinite;
}

/* Subtle bounce animation for action button */
@keyframes bounce-subtle {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-2px);
    }
}

.animate-bounce-subtle {
    animation: bounce-subtle 1s ease-in-out infinite;
}

/* Green pulse animation for resolved tickets needing confirmation */
@keyframes pulse-green {
    0%, 100% {
        background-color: rgb(240 253 244); /* green-50 */
    }
    50% {
        background-color: rgb(187 247 208); /* green-200 */
    }
}

.animate-pulse-green {
    animation: pulse-green 2s ease-in-out infinite;
}

/* Purple pulse animation for new/reopened tickets needing triage */
@keyframes pulse-purple {
    0%, 100% {
        background-color: rgb(250 245 255); /* purple-50 */
    }
    50% {
        background-color: rgb(233 213 255); /* purple-200 */
    }
}

.animate-pulse-purple {
    animation: pulse-purple 2s ease-in-out infinite;
}

/* Blue pulse animation for assigned tickets needing acceptance */
@keyframes pulse-blue {
    0%, 100% {
        background-color: rgb(239 246 255); /* blue-50 */
    }
    50% {
        background-color: rgb(191 219 254); /* blue-200 */
    }
}

.animate-pulse-blue {
    animation: pulse-blue 2s ease-in-out infinite;
}
</style>
