<script setup>
// import vue
import { ref } from 'vue'

// import inertia router
import { router } from '@inertiajs/vue3'

// import icons
import { Search } from 'lucide-vue-next'

// props
const props = defineProps({
    URL: {
        type: String,
        required: true,
    },
})

// define state search
const search = ref('')

// function "searchHandler"
const handleSearch = (e) => {
    e.preventDefault()

    // fetch to search
    router.get(`${props.URL}?q=${search.value}`)
};
</script>

<template>
    <form @submit="handleSearch" class="space-y-4">
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
                        placeholder="Cari data..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-gray-50 border border-gray-300 rounded-lg focus:outline-none transition-all duration-200"
                    />
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex space-x-3 mt-3 md:mt-0">
                <button
                    type="submit"
                    class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors duration-200"
                >
                    <Search class="w-4 h-4 inline mr-2" />
                    Search
                </button>
            </div>
        </div>
    </form>
</template>
