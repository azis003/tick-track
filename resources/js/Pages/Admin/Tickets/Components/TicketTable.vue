<script setup>
import { Link } from '@inertiajs/vue3'
import StatusBadge from './StatusBadge.vue'
import PriorityBadge from './PriorityBadge.vue'
import { Eye, ChevronLeft, ChevronRight } from 'lucide-vue-next'

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
    }
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
                            <Link
                                :href="`/admin/tickets/${ticket.id}`"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors"
                            >
                                <Eye class="h-4 w-4" />
                                <span>Lihat</span>
                            </Link>
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
