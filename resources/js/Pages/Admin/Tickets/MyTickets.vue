<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link } from '@inertiajs/vue3'

// import icons
import { Plus, Ticket, Briefcase } from 'lucide-vue-next'

// import permissions helper
import { hasPermission } from '@/Utils/Permissions'

// import components
import TicketFilters from './Components/TicketFilters.vue'
import TicketTable from './Components/TicketTable.vue'

// props
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
    },
    isStaff: {
        type: Boolean,
        default: false
    }
})
</script>

<template>
    <Head title="Tiket Saya" />

    <LayoutAdmin>
        <!-- Page Header -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <Briefcase v-if="isStaff" class="w-7 h-7 text-indigo-600" />
                    <Ticket v-else class="w-7 h-7 text-blue-600" />
                    Tiket Saya
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ isStaff ? 'Tiket yang ditugaskan kepada Anda' : 'Daftar tiket yang Anda buat' }}
                </p>
            </div>
            <!-- Only show create button for Pegawai -->
            <Link
                v-if="!isStaff && hasPermission('tickets.create')"
                href="/admin/tickets/create"
                class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-sm"
            >
                <Plus class="w-4 h-4 mr-2" />
                Buat Tiket Baru
            </Link>
        </div>

        <!-- Filters -->
        <TicketFilters
            :filters="filters"
            :statuses="statuses"
            route-name="admin.tickets.my-tickets"
        />

        <!-- Table -->
        <TicketTable
            :tickets="tickets"
            :statuses="statuses"
            :show-reporter="isStaff"
            :highlight-action-required="!isStaff"
            :show-work-action="isStaff"
        />
    </LayoutAdmin>
</template>
