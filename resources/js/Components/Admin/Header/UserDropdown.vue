<script setup>
// import Link dari Inertia
import { Link } from '@inertiajs/vue3'

// import icons dari lucide vue
import { User, Settings, LogOut, Shield } from 'lucide-vue-next'

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
})

// User navigation items - sesuai dengan arsitektur TickTrack
const userNavigation = [
    {
        name: 'Pengaturan',
        href: '/admin/settings',
        icon: Settings,
    },
    {
        name: 'Keluar',
        href: '/logout', // sesuai route di web.php
        method: 'post',
        icon: LogOut,
    },
];
</script>

<template>
    <div class="relative">
        <div class="flex items-center gap-3">
            <!-- User Info (Hidden on Mobile) -->
            <div class="hidden text-right sm:block">
                <p class="text-sm font-bold text-gray-900">
                    {{ auth.user?.name || 'User' }}
                </p>
                <p class="text-xs text-gray-500 font-medium">
                    {{ auth.user?.roles?.[0] || 'Staff' }}
                </p>
            </div>

            <!-- Avatar Button -->
            <div class="relative">
                <button
                    type="button"
                    @click="toggleDropdown('user')"
                    class="relative flex items-center max-w-xs text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-transform hover:scale-105 duration-200"
                >
                    <div class="relative w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-md">
                        <User class="w-5 h-5 text-white" />
                        <!-- Online Indicator -->
                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div>
                    </div>
                </button>

                <!-- User Dropdown Menu -->
                <transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="translate-y-2 opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="translate-y-2 opacity-0"
                >
                    <div
                        v-if="activeDropdown === 'user'"
                        class="absolute right-0 z-20 w-64 py-2 mt-4 origin-top-right bg-white rounded-2xl shadow-2xl border border-gray-100 ring-1 ring-black/5"
                    >
                        <!-- User Info Header -->
                        <div class="px-4 py-3 border-b border-gray-100 bg-slate-50/50 rounded-t-2xl">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-sm">
                                    <User class="w-5 h-5 text-white" />
                                </div>
                                <div class="ml-3 flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate">
                                        {{ auth.user?.name || 'User' }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">
                                        {{ auth.user?.email || '-' }}
                                    </p>
                                </div>
                            </div>
                            <!-- Role Badge -->
                            <div class="mt-2 flex items-center">
                                <Shield class="w-3 h-3 text-blue-500 mr-1.5" />
                                <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wide">
                                    {{ auth.user?.roles?.[0] || 'Staff' }}
                                </span>
                            </div>
                        </div>

                        <!-- Navigation Items -->
                        <div class="py-1 px-2">
                            <Link
                                v-for="item in userNavigation"
                                :key="item.name"
                                :href="item.href"
                                :method="item.method || 'get'"
                                as="button"
                                @click="() => toggleDropdown(null)"
                                class="flex items-center w-full px-3 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-all duration-150 rounded-xl group"
                            >
                                <div class="w-8 h-8 flex items-center justify-center bg-gray-50 rounded-lg mr-3 group-hover:bg-white group-hover:shadow-sm transition-all duration-200">
                                    <component
                                        :is="item.icon"
                                        class="w-4 h-4 text-gray-400 group-hover:text-blue-600 transition-colors duration-200"
                                    />
                                </div>
                                <span class="font-bold">
                                    {{ item.name }}
                                </span>
                            </Link>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>
