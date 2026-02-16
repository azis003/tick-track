<script setup>
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { ClipboardList, Search } from 'lucide-vue-next'
import TechnicianTicketTable from './Components/TechnicianTicketTable.vue'
import debounce from 'lodash/debounce'

/**
 * AssignedTickets Page
 * Halaman untuk Teknisi melihat tiket yang ditugaskan kepadanya
 * Sesuai wireframe 3.11 - Dashboard Teknisi
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
    statuses: {
        type: Object,
        default: () => ({})
    }
})

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')

const applyFilters = debounce(() => {
    router.get('/tickets/assigned', {
        search: search.value || undefined,
        status: status.value || undefined
    }, {
        preserveState: true,
        preserveScroll: true
    })
}, 300)

watch([search, status], applyFilters)
</script>

<template>
    <Head title="Tiket Ditugaskan" />

    <LayoutAdmin>
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-indigo-100 rounded-lg">
                    <ClipboardList class="w-6 h-6 text-indigo-600" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Tiket Saya</h1>
                    <p class="text-sm text-gray-500">
                        {{ tickets.total }} tiket ditugaskan kepada Anda
                    </p>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-sm text-blue-800">
                    <p class="font-medium">Petunjuk:</p>
                    <ul class="list-disc list-inside mt-1 space-y-1 text-blue-700">
                        <li><strong>Mulai</strong> - Klik untuk mulai mengerjakan tiket</li>
                        <li><strong>Kembalikan</strong> - Kembalikan ke Helpdesk jika tidak bisa dikerjakan</li>
                        <li><strong>Kerjakan</strong> - Lanjutkan mengerjakan tiket yang sudah dimulai</li>
                    </ul>
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
                            placeholder="Cari nomor tiket atau judul..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                        />
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="w-48">
                    <select
                        v-model="status"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 appearance-none bg-white"
                    >
                        <option value="">Semua Status</option>
                        <option v-for="(label, key) in statuses" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Technician Ticket Table -->
        <TechnicianTicketTable
            :tickets="tickets"
            :statuses="statuses"
        />
    </LayoutAdmin>
</template>
