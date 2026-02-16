<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link, router } from '@inertiajs/vue3'

// import vue
import { ref, watch } from 'vue'

// import shared components
import PageHeader from '@/Shared/PageHeader.vue'
import Delete from '@/Shared/Delete.vue'
import Pagination from '@/Shared/Pagination.vue'
import TableEmpty from '@/Shared/TableEmpty.vue'

// import icons
import { Search, Pencil, AlertTriangle, CheckCircle, XCircle, Ticket, TrendingUp } from 'lucide-vue-next'

// import permission helper
import { hasAnyPermission } from '@/Utils/Permissions'

// props
const props = defineProps({
    priorities: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
})

// search state
const search = ref(props.filters.search || '')

// debounce search
let searchTimeout = null
watch(search, (newValue) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(
            '/priorities',
            { search: newValue },
            { preserveState: true, replace: true }
        )
    }, 300)
})

// Badge color for status
const getStatusBadgeClass = (isActive) => {
    return isActive
        ? 'bg-green-100 text-green-800 border-green-200'
        : 'bg-red-100 text-red-800 border-red-200'
}

// Get dynamic badge color
const getPriorityBadgeStyle = (color) => {
    if (!color) return {}
    return {
        backgroundColor: color + '20', // 20 = opacity ~12%
        color: color,
        borderColor: color + '40',
    }
}

// Get emoji based on level
const getEmojiByLevel = (level) => {
    const emojiMap = {
        4: '🔴',
        3: '🟠',
        2: '🟡',
        1: '🟢'
    }
    return emojiMap[level] || '⚪'
}
</script>

<template>
    <Head title="Data Prioritas" />

    <LayoutAdmin>
        <!-- Page Header -->
        <PageHeader
            title="Data Prioritas"
            description="Kelola tingkat urgensi tiket untuk pengelolaan SLA"
            :showButton="true"
            action="/priorities/create"
            actionText="Tambah Prioritas"
            permission="priorities.create"
        />

        <!-- Search & Filter Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
            <div class="p-4">
                <div class="flex flex-col md:flex-row md:items-center md:space-x-4">
                    <!-- Search input -->
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <Search class="w-5 h-5 text-gray-400" />
                            </div>
                            <input
                                type="search"
                                v-model="search"
                                placeholder="Cari nama atau deskripsi prioritas..."
                                class="w-full pl-10 pr-4 py-2.5 text-sm bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">
                                No
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                                Level
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Prioritas
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Deskripsi
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                                Tiket
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Data rows -->
                        <tr
                            v-for="(priority, index) in priorities.data"
                            :key="priority.id"
                            class="hover:bg-gray-50 transition-colors duration-150"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ (priorities.current_page - 1) * priorities.per_page + index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <TrendingUp class="w-4 h-4 text-gray-400 mr-2" />
                                    <span class="text-lg font-bold text-gray-900">
                                        {{ priority.level }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <!-- Priority Badge with Color -->
                                    <span
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium border"
                                        :style="getPriorityBadgeStyle(priority.color)"
                                    >
                                        <span class="text-lg mr-2">{{ getEmojiByLevel(priority.level) }}</span>
                                        {{ priority.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <div class="max-w-xs line-clamp-2">
                                    {{ priority.description || '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex flex-col gap-1">
                                    <!-- Total Tickets -->
                                    <span 
                                        class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-lg text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200"
                                        :title="`User Priority: ${priority.user_priority_tickets_count} | Final Priority: ${priority.final_priority_tickets_count}`"
                                    >
                                        <Ticket class="w-3.5 h-3.5 mr-1" />
                                        {{ priority.user_priority_tickets_count + priority.final_priority_tickets_count }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium border',
                                        getStatusBadgeClass(priority.is_active)
                                    ]"
                                >
                                    <CheckCircle v-if="priority.is_active" class="w-3.5 h-3.5 mr-1" />
                                    <XCircle v-else class="w-3.5 h-3.5 mr-1" />
                                    {{ priority.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Edit Button -->
                                    <Link
                                        v-if="hasAnyPermission(['priorities.edit'])"
                                        :href="`/priorities/${priority.id}/edit`"
                                        class="inline-flex items-center p-2 bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white rounded-lg transition-all duration-200 shadow-sm"
                                        title="Edit"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </Link>

                                    <!-- Delete Button -->
                                    <Delete
                                        v-if="hasAnyPermission(['priorities.delete'])"
                                        URL="/priorities"
                                        :id="priority.id"
                                    />
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <TableEmpty
                            v-if="priorities.data?.length === 0"
                            title="Tidak ada data prioritas"
                            description="Belum ada prioritas yang ditambahkan atau tidak ditemukan hasil pencarian."
                            :colSpan="7"
                        />
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="priorities.data?.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p class="text-sm text-gray-600">
                        Menampilkan
                        <span class="font-semibold">{{ priorities.from }}</span>
                        -
                        <span class="font-semibold">{{ priorities.to }}</span>
                        dari
                        <span class="font-semibold">{{ priorities.total }}</span>
                        data
                    </p>
                    <Pagination :links="priorities.links" />
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>
