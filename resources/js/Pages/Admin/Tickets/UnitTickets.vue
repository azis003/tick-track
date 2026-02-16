<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link } from '@inertiajs/vue3'

// import icons
import { Plus, Users } from 'lucide-vue-next'

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
    }
})
</script>

<template>
    <Head title="Tiket Unit" />

    <LayoutAdmin>
        <!-- Page Header -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <Users class="w-7 h-7 text-blue-600" />
                    Tiket Unit Saya
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Daftar tiket dari anggota unit Anda
                </p>
            </div>
            <Link
                v-if="hasPermission('tickets.create')"
                href="/tickets/create"
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
            route-name="tickets.unit"
        />

        <!-- Table -->
        <TicketTable
            :tickets="tickets"
            :statuses="statuses"
            :show-reporter="true"
        />
    </LayoutAdmin>
</template>
