<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Search, Filter, X } from 'lucide-vue-next'

/**
 * TicketFilters Component
 * Filter dan search untuk daftar tiket
 */
const props = defineProps({
    filters: {
        type: Object,
        default: () => ({})
    },
    statuses: {
        type: Object,
        default: () => ({})
    },
    categories: {
        type: Array,
        default: () => []
    },
    priorities: {
        type: Array,
        default: () => []
    },
    routeName: {
        type: String,
        required: true
    }
})

// Route name to URL mapping
const routeUrls = {
    'admin.tickets.index': '/admin/tickets',
    'admin.tickets.my-tickets': '/admin/tickets/my-tickets',
    'admin.tickets.unit': '/admin/tickets/unit',
}

const getUrl = () => routeUrls[props.routeName] || '/admin/tickets'

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const categoryId = ref(props.filters.category_id || '')
const priorityId = ref(props.filters.priority_id || '')

const applyFilters = () => {
    const params = {}
    if (search.value) params.search = search.value
    if (status.value) params.status = status.value
    if (categoryId.value) params.category_id = categoryId.value
    if (priorityId.value) params.priority_id = priorityId.value

    router.get(getUrl(), params, {
        preserveState: true,
        preserveScroll: true
    })
}

const clearFilters = () => {
    search.value = ''
    status.value = ''
    categoryId.value = ''
    priorityId.value = ''
    router.get(getUrl(), {}, {
        preserveState: true,
        preserveScroll: true
    })
}

const hasFilters = () => {
    return search.value || status.value || categoryId.value || priorityId.value
}

// Debounce search
let searchTimeout = null
watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(applyFilters, 300)
})
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari tiket..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
            </div>

            <!-- Status filter -->
            <div class="w-full md:w-48">
                <select
                    v-model="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    @change="applyFilters"
                >
                    <option value="">Semua Status</option>
                    <option v-for="(label, key) in statuses" :key="key" :value="key">
                        {{ label }}
                    </option>
                </select>
            </div>

            <!-- Category filter (if available) -->
            <div v-if="categories.length > 0" class="w-full md:w-48">
                <select
                    v-model="categoryId"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    @change="applyFilters"
                >
                    <option value="">Semua Kategori</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                    </option>
                </select>
            </div>

            <!-- Priority filter (if available) -->
            <div v-if="priorities.length > 0" class="w-full md:w-48">
                <select
                    v-model="priorityId"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    @change="applyFilters"
                >
                    <option value="">Semua Prioritas</option>
                    <option v-for="prio in priorities" :key="prio.id" :value="prio.id">
                        {{ prio.name }}
                    </option>
                </select>
            </div>

            <!-- Clear filters -->
            <button
                v-if="hasFilters()"
                type="button"
                class="flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
                @click="clearFilters"
            >
                <X class="h-4 w-4" />
                <span class="hidden md:inline">Reset</span>
            </button>
        </div>
    </div>
</template>
