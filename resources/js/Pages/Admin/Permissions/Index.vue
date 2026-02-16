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
import { Search, Pencil, Shield } from 'lucide-vue-next'

// import permission helper
import { hasAnyPermission } from '@/Utils/Permissions'

// props
const props = defineProps({
    permissions: {
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
            '/permissions',
            { search: newValue },
            { preserveState: true, replace: true }
        )
    }, 300)
})
</script>

<template>
    <Head title="Data Permission" />

    <LayoutAdmin>
        <!-- Page Header -->
        <PageHeader
            title="Data Permission"
            description="Kelola daftar permission sistem"
            :showButton="true"
            action="/permissions/create"
            actionText="Tambah Permission"
            permission="permissions.create"
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
                                placeholder="Cari permission..."
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
                                Nama Permission
                            </th>
                            <!-- <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">
                                Guard
                            </th> -->
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-36">
                                Digunakan Role
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Data rows -->
                        <tr
                            v-for="(permission, index) in permissions.data"
                            :key="permission.id"
                            class="hover:bg-gray-50 transition-colors duration-150"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ (permissions.current_page - 1) * permissions.per_page + index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <!-- <div class="flex-shrink-0 h-8 w-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                        <Shield class="w-4 h-4 text-indigo-600" />
                                    </div> -->
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900 font-mono">
                                            {{ permission.name }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <!-- <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ permission.guard_name }}
                                </span>
                            </td> -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        permission.roles_count > 0
                                            ? 'bg-blue-100 text-blue-800'
                                            : 'bg-gray-100 text-gray-500'
                                    ]"
                                >
                                    {{ permission.roles_count }} role{{ permission.roles_count !== 1 ? 's' : '' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Edit Button -->
                                    <Link
                                        v-if="hasAnyPermission(['permissions.edit'])"
                                        :href="`/permissions/${permission.id}/edit`"
                                        class="inline-flex items-center p-2 bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white rounded-lg transition-all duration-200 shadow-sm"
                                        title="Edit"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </Link>

                                    <!-- Delete Button -->
                                    <Delete
                                        v-if="hasAnyPermission(['permissions.delete'])"
                                        URL="/permissions"
                                        :id="permission.id"
                                    />
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <TableEmpty
                            v-if="permissions.data?.length === 0"
                            title="Tidak ada data permission"
                            description="Belum ada permission yang ditambahkan atau tidak ditemukan hasil pencarian."
                            :colSpan="5"
                        />
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="permissions.data?.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p class="text-sm text-gray-600">
                        Menampilkan
                        <span class="font-semibold">{{ permissions.from }}</span>
                        -
                        <span class="font-semibold">{{ permissions.to }}</span>
                        dari
                        <span class="font-semibold">{{ permissions.total }}</span>
                        data
                    </p>
                    <Pagination :links="permissions.links" />
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>
