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
import { Search, Pencil, Shield, Users, Key } from 'lucide-vue-next'

// import permission helper
import { hasAnyPermission } from '@/Utils/Permissions'

// props
const props = defineProps({
    roles: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    protectedRoles: {
        type: Array,
        default: () => [],
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
            '/admin/roles',
            { search: newValue },
            { preserveState: true, replace: true }
        )
    }, 300)
})

// Role badge colors
const getRoleBadgeClass = (roleName) => {
    const colors = {
        'super_admin': 'bg-red-100 text-red-800 border-red-200',
        'manager': 'bg-purple-100 text-purple-800 border-purple-200',
        'helpdesk': 'bg-blue-100 text-blue-800 border-blue-200',
        'teknisi': 'bg-green-100 text-green-800 border-green-200',
        'ketua_tim': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'pegawai': 'bg-gray-100 text-gray-800 border-gray-200',
    }
    return colors[roleName] || 'bg-slate-100 text-slate-800 border-slate-200'
}

// Check if role is protected
const isProtected = (roleName) => {
    return props.protectedRoles.includes(roleName)
}
</script>

<template>
    <Head title="Data Role" />

    <LayoutAdmin>
        <!-- Page Header -->
        <PageHeader
            title="Data Role"
            description="Kelola daftar role dan permissions pengguna"
            :showButton="true"
            action="/admin/roles/create"
            actionText="Tambah Role"
            permission="roles.create"
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
                                placeholder="Cari role..."
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
                                Nama Role
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-36">
                                Permissions
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                                Users
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Data rows -->
                        <tr
                            v-for="(role, index) in roles.data"
                            :key="role.id"
                            class="hover:bg-gray-50 transition-colors duration-150"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ (roles.current_page - 1) * roles.per_page + index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold border',
                                            getRoleBadgeClass(role.name)
                                        ]"
                                    >
                                        <!-- <Shield class="w-4 h-4 mr-2" /> -->
                                        {{ role.name }}
                                    </span>
                                    <span
                                        v-if="isProtected(role.name)"
                                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700"
                                    >
                                        Protected
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-sm font-medium bg-indigo-50 text-indigo-700">
                                    <!-- <Key class="w-3.5 h-3.5 mr-1.5" /> -->
                                    {{ role.permissions_count }} izin
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-1 rounded-lg text-sm font-medium',
                                        role.users_count > 0
                                            ? 'bg-blue-50 text-blue-700'
                                            : 'bg-gray-50 text-gray-500'
                                    ]"
                                >
                                    <Users class="w-3.5 h-3.5 mr-1.5" />
                                    {{ role.users_count }} user{{ role.users_count !== 1 ? 's' : '' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Edit Button -->
                                    <Link
                                        v-if="hasAnyPermission(['roles.edit'])"
                                        :href="`/admin/roles/${role.id}/edit`"
                                        class="inline-flex items-center p-2 bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white rounded-lg transition-all duration-200 shadow-sm"
                                        title="Edit"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </Link>

                                    <!-- Delete Button (disabled for protected roles) -->
                                    <Delete
                                        v-if="hasAnyPermission(['roles.delete']) && !isProtected(role.name)"
                                        URL="/admin/roles"
                                        :id="role.id"
                                    />
                                    <span
                                        v-else-if="hasAnyPermission(['roles.delete']) && isProtected(role.name)"
                                        class="inline-flex items-center p-2 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed"
                                        title="Role ini tidak dapat dihapus"
                                    >
                                        🔒
                                    </span>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <TableEmpty
                            v-if="roles.data?.length === 0"
                            title="Tidak ada data role"
                            description="Belum ada role yang ditambahkan atau tidak ditemukan hasil pencarian."
                            :colSpan="5"
                        />
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="roles.data?.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p class="text-sm text-gray-600">
                        Menampilkan
                        <span class="font-semibold">{{ roles.from }}</span>
                        -
                        <span class="font-semibold">{{ roles.to }}</span>
                        dari
                        <span class="font-semibold">{{ roles.total }}</span>
                        data
                    </p>
                    <Pagination :links="roles.links" />
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>
