<script setup>
// import usePage
import { Link, usePage } from '@inertiajs/vue3'

// import icons dari lucide vue
import { User, Settings, LogOut, ChevronDown } from 'lucide-vue-next'

// import menuConfig yang sudah kita buat
import { getFilteredMenuItems, getFilteredDropdown } from './menuConfig'

// props - Logika dikontrol oleh parent (Header)
defineProps({
    auth: {
        type: Object,
        required: true,
    },
    activeDropdown: {
        type: String,
        default: null,
    },
    toggleDropdown: {
        type: Function,
        required: true,
    },
    handleDropdownItemClick: {
        type: Function,
        required: true,
    },
    setIsMobileMenuOpen: {
        type: Function,
        required: true,
    },
})

const { props } = usePage()
const pendingCount = props.auth.pending_user_count || 0
const taskQueueCount = props.auth.task_queue_count || 0

// Filter menu items berdasarkan permission user (sesuai seeder_planning.md)
const filteredMenuItems = getFilteredMenuItems()

// User navigation items - sesuai dengan frontend_planning.md
const userNavigation = [
    {
        name: 'Pengaturan',
        href: '/admin/settings',
        icon: Settings,
    },
    {
        name: 'Keluar',
        href: '/logout',
        method: 'post',
        icon: LogOut,
    },
];
</script>

<template>
    <!-- Mobile Menu Container - Hanya tampil di layar kecil -->
    <div class="border-t border-gray-200 lg:hidden bg-white/95 backdrop-blur-lg">
        <!-- Mobile Menu Items -->
        <div class="px-2 py-3 space-y-1">
            <template v-for="item in filteredMenuItems" :key="item.name">
                <div>
                    <!-- CASE 1: Menu dengan Dropdown -->
                    <template v-if="item.dropdown">
                        <template v-if="getFilteredDropdown(item.dropdown).length > 0">
                            <div class="space-y-1">
                                <button
                                    type="button"
                                    @click="toggleDropdown(item.name)"
                                    :class="`flex items-center w-full px-4 py-3 text-base font-bold rounded-xl transition-all duration-200 ${
                                        activeDropdown === item.name
                                            ? 'bg-gradient-to-r from-blue-50 to-blue-100 text-blue-700'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                                    }`"
                                >
                                    <div class="relative">
                                        <component :is="item.icon" class="w-5 h-5 mr-3" />
                                        <!-- Notification Dot for Tiket Menu -->
                                        <span 
                                            v-if="item.name === 'Tiket' && (pendingCount > 0 || taskQueueCount > 0)"
                                            class="absolute -top-1 right-1.5 flex h-3 w-3"
                                        >
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500 border-2 border-white"></span>
                                        </span>
                                    </div>
                                    {{ item.name }}
                                    <ChevronDown
                                        :class="`w-4 h-4 ml-auto transition-transform duration-200 ${
                                            activeDropdown === item.name ? 'rotate-180 text-blue-600' : 'text-gray-400'
                                        }`"
                                    />
                                </button>

                                <!-- Sub-menu Dropdown -->
                                <transition
                                    enter-active-class="transition duration-150 ease-out"
                                    enter-from-class="opacity-0 -translate-y-1"
                                    enter-to-class="opacity-100 translate-y-0"
                                    leave-active-class="transition duration-100 ease-in"
                                    leave-from-class="opacity-100 translate-y-0"
                                    leave-to-class="opacity-0 -translate-y-1"
                                >
                                    <div
                                        v-if="activeDropdown === item.name"
                                        class="ml-4 mr-2 space-y-1 bg-slate-50 rounded-xl p-2 border border-gray-100"
                                    >
                                        <Link
                                            v-for="subItem in getFilteredDropdown(item.dropdown)"
                                            :key="subItem.name"
                                            :href="subItem.href"
                                            @click="handleDropdownItemClick"
                                            class="flex items-start px-4 py-3 text-sm font-medium text-gray-600 rounded-lg hover:bg-white hover:text-gray-900 hover:shadow-sm transition-all duration-150 group"
                                        >
                                            <div class="w-9 h-9 flex items-center justify-center bg-white border border-gray-100 rounded-lg mr-3 group-hover:border-blue-200 group-hover:bg-blue-50 transition-all duration-200">
                                                <component
                                                    :is="subItem.icon"
                                                    class="w-4 h-4 text-gray-400 group-hover:text-blue-600"
                                                />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between">
                                                    <div class="font-bold text-slate-800 group-hover:text-blue-700">
                                                        {{ subItem.name }}
                                                    </div>
                                                    <!-- Badge for Tiket Saya -->
                                                    <span 
                                                        v-if="subItem.name === 'Tiket Saya' && pendingCount > 0"
                                                        class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold text-white bg-red-500 rounded-full"
                                                    >
                                                        {{ pendingCount }}
                                                    </span>
                                                    <!-- Badge for Daftar Tugas -->
                                                    <span 
                                                        v-if="subItem.name === 'Daftar Tugas' && taskQueueCount > 0"
                                                        class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold text-white bg-orange-500 rounded-full"
                                                    >
                                                        {{ taskQueueCount }}
                                                    </span>
                                                </div>
                                                <div v-if="subItem.description" class="text-[11px] text-gray-500 mt-0.5 leading-relaxed">
                                                    {{ subItem.description }}
                                                </div>
                                            </div>
                                        </Link>
                                    </div>
                                </transition>
                            </div>
                        </template>
                    </template>

                    <!-- CASE 2: Single Link Menu -->
                    <template v-else>
                        <Link
                            :href="item.href"
                            @click="handleDropdownItemClick"
                            :class="`flex items-center px-4 py-3 text-base font-bold rounded-xl transition-all duration-200 ${
                                item.current
                                    ? 'bg-gradient-to-r from-blue-50 to-blue-100 text-blue-700'
                                    : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                            }`"
                        >
                            <component :is="item.icon" class="w-5 h-5 mr-3" />
                            {{ item.name }}
                        </Link>
                    </template>
                </div>
            </template>
        </div>

        <!-- Mobile User Info Section -->
        <div class="px-4 py-4 border-t border-gray-100 bg-slate-50/80">
            <div class="flex items-center">
                <!-- Avatar User -->
                <div class="relative w-11 h-11 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-sm">
                    <User class="w-5 h-5 text-white" />
                </div>
                <!-- Info User -->
                <div class="ml-3 flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900 truncate">
                        {{ auth.user?.name || 'User' }}
                    </p>
                    <p class="text-xs text-gray-500 truncate">
                        {{ auth.user?.email || '-' }}
                    </p>
                </div>
            </div>

            <!-- User Navigation Buttons -->
            <div class="grid grid-cols-2 gap-2 mt-4">
                <Link
                    v-for="item in userNavigation"
                    :key="item.name"
                    :href="item.href"
                    :method="item.method || 'get'"
                    as="button"
                    @click="() => setIsMobileMenuOpen(false)"
                    class="flex items-center justify-center px-3 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all duration-150"
                >
                    <component :is="item.icon" class="w-4 h-4 mr-2" />
                    {{ item.name }}
                </Link>
            </div>
        </div>
    </div>
</template>
