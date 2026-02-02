<script setup>
// import Link dari Inertia
import { Link } from '@inertiajs/vue3'

// import icons dari lucide vue
import { ChevronDown } from 'lucide-vue-next'

// import menuConfig
import { getFilteredMenuItems, getFilteredDropdown } from './menuConfig'

// import MenuDropdown component
import MenuDropdown from './MenuDropdown.vue'

// props - Logika buka/tutup diatur oleh parent (Header)
defineProps({
    activeDropdown: {
        type: String,
        default: null,
    },
    toggleDropdown: {
        type: Function,
        required: true,
    },
    closeAllDropdowns: {
        type: Function,
        required: true,
    },
})

// Filter menu items berdasarkan permission user sesuai planning
const filteredMenuItems = getFilteredMenuItems();
</script>

<template>
    <div class="hidden lg:flex items-center space-x-1">
        <template v-for="item in filteredMenuItems" :key="item.name">
            <div class="relative">
                <!-- CASE 1: Menu dengan Dropdown -->
                <template v-if="item.dropdown && item.dropdown.length > 0">
                    <div class="relative">
                        <button
                            type="button"
                            @click="toggleDropdown(item.name)"
                            :class="`inline-flex items-center px-4 py-2.5 text-sm font-bold rounded-xl transition-all duration-200 hover:bg-gray-50 hover:text-gray-900 border border-transparent hover:border-gray-200 ${activeDropdown === item.name
                                    ? 'bg-gradient-to-r from-blue-50 to-blue-100 text-blue-700 border border-blue-200 shadow-sm'
                                    : 'text-gray-700'
                                }`"
                        >
                            <component :is="item.icon" class="w-5 h-5 mr-2.5" />
                            {{ item.name }}
                            <ChevronDown
                                :class="`w-5 h-5 ml-2 transition-transform duration-200 ${activeDropdown === item.name
                                        ? 'rotate-180 text-blue-600'
                                        : 'text-gray-400'
                                    }`"
                            />
                        </button>

                        <!-- Komponen Dropdown -->
                        <transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="translate-y-2 opacity-0"
                            enter-to-class="translate-y-0 opacity-100"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="translate-y-0 opacity-100"
                            leave-to-class="translate-y-2 opacity-0"
                        >
                            <MenuDropdown
                                v-if="activeDropdown === item.name && getFilteredDropdown(item.dropdown).length > 0"
                                :item="item"
                                :filteredDropdown="getFilteredDropdown(item.dropdown)"
                                :closeDropdown="() => toggleDropdown(null)"
                            />
                        </transition>
                    </div>
                </template>

                <!-- CASE 2: Single Link Menu -->
                <template v-else>
                    <Link
                        :href="item.href"
                        @click="closeAllDropdowns"
                        :class="`inline-flex items-center px-4 py-2.5 text-sm font-bold rounded-xl transition-all duration-200 hover:bg-gray-50 hover:text-gray-900 border border-transparent hover:border-gray-200 ${item.current
                                ? 'bg-gradient-to-r from-blue-50 to-blue-100 text-blue-700 border border-blue-200 shadow-sm'
                                : 'text-gray-700'
                            }`"
                    >
                        <component :is="item.icon" class="w-5 h-5 mr-2.5" />
                        {{ item.name }}
                    </Link>
                </template>
            </div>
        </template>
    </div>
</template>
