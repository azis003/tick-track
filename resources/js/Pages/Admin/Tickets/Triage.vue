<script setup>
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { AlertTriangle, Search, Filter, Eye, RotateCcw } from 'lucide-vue-next'
import StatusBadge from './Components/StatusBadge.vue'
import PriorityBadge from './Components/PriorityBadge.vue'
import debounce from 'lodash/debounce'

/**
 * Triage Page
 * Halaman untuk Helpdesk melihat dan melakukan triage tiket baru
 */
const props = defineProps({
    tickets: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    categories: {
        type: Array,
        default: () => []
    }
})

const search = ref(props.filters.search || '')
const categoryId = ref(props.filters.category_id || '')

const applyFilters = debounce(() => {
    router.get('/admin/tickets/triage', {
        search: search.value || undefined,
        category_id: categoryId.value || undefined
    }, {
        preserveState: true,
        preserveScroll: true
    })
}, 300)

watch([search, categoryId], applyFilters)

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
    <Head title="Triage Tiket" />

    <LayoutAdmin>
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <AlertTriangle class="w-6 h-6 text-purple-600" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Triage Tiket</h1>
                    <p class="text-sm text-gray-500">
                        {{ tickets.total }} tiket menunggu verifikasi
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <div class="flex flex-wrap gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[240px]">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nomor tiket, judul, atau pelapor..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                        />
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="w-48">
                    <div class="relative">
                        <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <select
                            v-model="categoryId"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 appearance-none bg-white"
                        >
                            <option value="">Semua Kategori</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
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
                                Urgensi User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pelapor
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Waktu Masuk
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
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
                                'transition-colors',
                                ticket.return_reason ? 'bg-orange-50/50 hover:bg-orange-100/50' : 'hover:bg-purple-50/50'
                            ]"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <RotateCcw v-if="ticket.return_reason" class="w-4 h-4 text-orange-500" />
                                    <Link
                                        :href="`/admin/tickets/${ticket.id}`"
                                        class="text-purple-600 hover:text-purple-800 font-mono text-sm font-medium"
                                    >
                                        {{ ticket.ticket_number }}
                                    </Link>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 line-clamp-2 max-w-xs">
                                    {{ ticket.title }}
                                </div>
                                <p v-if="ticket.return_reason" class="text-xs text-orange-600 mt-1 italic">
                                    Alasan: {{ ticket.return_reason }}
                                </p>
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
                                <span class="text-sm text-gray-600">
                                    {{ ticket.reporter?.name || '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(ticket.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <!-- Dynamic status badge -->
                                <span 
                                    v-if="ticket.return_reason" 
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700"
                                >
                                    <RotateCcw class="w-3 h-3" />
                                    Dikembalikan
                                </span>
                                <StatusBadge
                                    v-else
                                    :status="ticket.status"
                                    :label="ticket.status === 'new' ? 'Baru' : (ticket.status === 'reopened' ? 'Dibuka Ulang' : 'Verifikasi')"
                                />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <Link
                                    :href="`/admin/tickets/${ticket.id}`"
                                    :class="[
                                        'inline-flex items-center gap-1 px-3 py-1.5 text-sm rounded-lg transition-colors',
                                        ticket.return_reason 
                                            ? 'bg-orange-600 text-white hover:bg-orange-700' 
                                            : 'bg-purple-600 text-white hover:bg-purple-700'
                                    ]"
                                >
                                    <Eye class="h-4 w-4" />
                                    <span>{{ ticket.return_reason ? 'Re-assign' : 'Triage' }}</span>
                                </Link>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="!tickets.data || tickets.data.length === 0">
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="p-4 bg-green-100 rounded-full mb-4">
                                        <AlertTriangle class="h-8 w-8 text-green-600" />
                                    </div>
                                    <h3 class="text-sm font-medium text-gray-900">Tidak ada tiket menunggu triage</h3>
                                    <p class="text-sm text-gray-500 mt-1">Semua tiket sudah diverifikasi 🎉</p>
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
                        class="px-3 py-1.5 text-sm border rounded-lg hover:bg-gray-100 transition-colors"
                        preserve-scroll
                    >
                        Prev
                    </Link>
                    <Link
                        v-if="tickets.next_page_url"
                        :href="tickets.next_page_url"
                        class="px-3 py-1.5 text-sm border rounded-lg hover:bg-gray-100 transition-colors"
                        preserve-scroll
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>
