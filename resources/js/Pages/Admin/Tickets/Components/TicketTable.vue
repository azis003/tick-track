<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import StatusBadge from './StatusBadge.vue'
import PriorityBadge from './PriorityBadge.vue'
import { Eye, ClipboardCheck, MessageCircle, ChevronLeft, ChevronRight, Wrench, CheckCircle } from 'lucide-vue-next'

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
    showWorkAction: {
        type: Boolean,
        default: false
    },
    highlightTriageRequired: {
        type: Boolean,
        default: false
    }
})

const page = usePage()
const auth = computed(() => page.props.auth)

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
                                <!-- View Button (only in Semua Tiket, not in Tiket Saya) -->
                                <Link
                                    v-if="!showWorkAction && !highlightActionRequired"
                                    :href="`/admin/tickets/${ticket.id}`"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors"
                                >
                                    <Eye class="h-4 w-4" />
                                    <span>Lihat</span>
                                </Link>
                                <!-- Verify Button (only for Helpdesk on new/reopened tickets, and only in Daftar Tugas) -->
                                <Link
                                    v-if="highlightTriageRequired && canTriage && needsVerification(ticket)"
                                    :href="`/admin/tickets/${ticket.id}?action=triage`"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors font-medium shadow-sm"
                                >
                                    <ClipboardCheck class="h-4 w-4" />
                                    <span>Verifikasi</span>
                                </Link>
                                <!-- Work Button (for staff's assigned tickets) -->
                                <Link
                                    v-if="showWorkAction && canWork(ticket)"
                                    :href="`/admin/tickets/${ticket.id}?action=work`"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors font-medium shadow-sm"
                                >
                                    <Wrench class="h-4 w-4" />
                                    <span>Kerjakan</span>
                                </Link>
                                <!-- Respond Button (for reporter on pending_user tickets) -->
                                <Link
                                    v-if="highlightActionRequired && needsReporterAction(ticket)"
                                    :href="`/admin/tickets/${ticket.id}`"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors font-medium shadow-sm animate-bounce-subtle"
                                >
                                    <MessageCircle class="h-4 w-4" />
                                    <span>Tanggapi</span>
                                </Link>
                                <!-- Confirm Button (for reporter on resolved tickets) -->
                                <Link
                                    v-if="highlightActionRequired && needsConfirmation(ticket)"
                                    :href="`/admin/tickets/${ticket.id}`"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors font-medium shadow-sm animate-bounce-subtle"
                                >
                                    <CheckCircle class="h-4 w-4" />
                                    <span>Konfirmasi</span>
                                </Link>
                                <!-- View Button for Pegawai (fallback when no special action needed) -->
                                <Link
                                    v-if="highlightActionRequired && !needsReporterAction(ticket) && !needsConfirmation(ticket)"
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
