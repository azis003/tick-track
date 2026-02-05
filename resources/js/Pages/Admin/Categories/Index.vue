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
import { Search, Pencil, Tag, CheckCircle, XCircle, Ticket } from 'lucide-vue-next'

// import permission helper
import { hasAnyPermission } from '@/Utils/Permissions'

// props
const props = defineProps({
    categories: {
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
            '/admin/categories',
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
const getCategoryBadgeStyle = (color) => {
    if (!color) return {}
    return {
        backgroundColor: color + '20', // 20 = opacity ~12%
        color: color,
        borderColor: color + '40',
    }
}
</script>

<template>
    <Head title="Data Kategori" />

    <LayoutAdmin>
        <!-- Page Header -->
        <PageHeader
            title="Data Kategori"
            description="Kelola kategori tiket untuk klasifikasi permasalahan"
            :showButton="true"
            action="/admin/categories/create"
            actionText="Tambah Kategori"
            permission="categories.create"
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
                                placeholder="Cari nama atau deskripsi kategori..."
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
                                Kategori
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Deskripsi
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">
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
                            v-for="(category, index) in categories.data"
                            :key="category.id"
                            class="hover:bg-gray-50 transition-colors duration-150"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ (categories.current_page - 1) * categories.per_page + index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <!-- Category Badge with Color -->
                                    <span
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium border"
                                        :style="category.color ? getCategoryBadgeStyle(category.color) : {}"
                                        :class="!category.color ? 'bg-gray-100 text-gray-800 border-gray-200' : ''"
                                    >
                                        <Tag class="w-4 h-4 mr-2" />
                                        {{ category.name }}
                                    </span>

                                    <!-- Icon if exists -->
                                    <span v-if="category.icon" class="ml-2 text-xs text-gray-400">
                                        {{ category.icon }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <div class="max-w-xs line-clamp-2">
                                    {{ category.description || '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                    <Ticket class="w-3.5 h-3.5 mr-1" />
                                    {{ category.tickets_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium border',
                                        getStatusBadgeClass(category.is_active)
                                    ]"
                                >
                                    <CheckCircle v-if="category.is_active" class="w-3.5 h-3.5 mr-1" />
                                    <XCircle v-else class="w-3.5 h-3.5 mr-1" />
                                    {{ category.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Edit Button -->
                                    <Link
                                        v-if="hasAnyPermission(['categories.edit'])"
                                        :href="`/admin/categories/${category.id}/edit`"
                                        class="inline-flex items-center p-2 bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white rounded-lg transition-all duration-200 shadow-sm"
                                        title="Edit"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </Link>

                                    <!-- Delete Button -->
                                    <Delete
                                        v-if="hasAnyPermission(['categories.delete'])"
                                        URL="/admin/categories"
                                        :id="category.id"
                                    />
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <TableEmpty
                            v-if="categories.data?.length === 0"
                            title="Tidak ada data kategori"
                            description="Belum ada kategori yang ditambahkan atau tidak ditemukan hasil pencarian."
                            :colSpan="6"
                        />
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="categories.data?.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p class="text-sm text-gray-600">
                        Menampilkan
                        <span class="font-semibold">{{ categories.from }}</span>
                        -
                        <span class="font-semibold">{{ categories.to }}</span>
                        dari
                        <span class="font-semibold">{{ categories.total }}</span>
                        data
                    </p>
                    <Pagination :links="categories.links" />
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>
