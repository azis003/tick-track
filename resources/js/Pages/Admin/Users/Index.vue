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
import { Search, Pencil, Users, CheckCircle, XCircle } from 'lucide-vue-next'

// import permission helper
import { hasAnyPermission } from '@/Utils/Permissions'

// props
const props = defineProps({
    users: {
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
            '/users',
            { search: newValue },
            { preserveState: true, replace: true }
        )
    }, 300)
})

// Get role names
const getRoleNames = (roles) => {
    if (!roles || roles.length === 0) return '-'
    return roles.map(role => role.name).join(', ')
}

// Badge color for status
const getStatusBadgeClass = (isActive) => {
    return isActive
        ? 'bg-green-100 text-green-800 border-green-200'
        : 'bg-red-100 text-red-800 border-red-200'
}
</script>

<template>
    <Head title="Data User" />

    <LayoutAdmin>
        <!-- Page Header -->
        <PageHeader
            title="Data User"
            description="Kelola data pengguna dan role akses sistem"
            :showButton="true"
            action="/users/create"
            actionText="Tambah User"
            permission="users.create"
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
                                placeholder="Cari nama, email, atau NIP..."
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
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Nama / NIP
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Email
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Unit Kerja
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Role
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
                            v-for="(user, index) in users.data"
                            :key="user.id"
                            class="hover:bg-gray-50 transition-colors duration-150"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ (users.current_page - 1) * users.per_page + index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                            <Users class="w-5 h-5 text-white" />
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ user.name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            NIP: {{ user.nip || '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ user.email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ user.unit?.name || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ getRoleNames(user.roles) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium border',
                                        getStatusBadgeClass(user.is_active)
                                    ]"
                                >
                                    <CheckCircle v-if="user.is_active" class="w-3.5 h-3.5 mr-1" />
                                    <XCircle v-else class="w-3.5 h-3.5 mr-1" />
                                    {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Edit Button -->
                                    <Link
                                        v-if="hasAnyPermission(['users.edit'])"
                                        :href="`/users/${user.id}/edit`"
                                        class="inline-flex items-center p-2 bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white rounded-lg transition-all duration-200 shadow-sm"
                                        title="Edit"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </Link>

                                    <!-- Delete Button -->
                                    <Delete
                                        v-if="hasAnyPermission(['users.delete'])"
                                        URL="/users"
                                        :id="user.id"
                                    />
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <TableEmpty
                            v-if="users.data?.length === 0"
                            title="Tidak ada data user"
                            description="Belum ada user yang ditambahkan atau tidak ditemukan hasil pencarian."
                            :colSpan="7"
                        />
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.data?.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p class="text-sm text-gray-600">
                        Menampilkan
                        <span class="font-semibold">{{ users.from }}</span>
                        -
                        <span class="font-semibold">{{ users.to }}</span>
                        dari
                        <span class="font-semibold">{{ users.total }}</span>
                        data
                    </p>
                    <Pagination :links="users.links" />
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>
