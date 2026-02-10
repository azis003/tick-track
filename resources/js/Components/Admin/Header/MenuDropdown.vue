<script setup>
// import usePage
import { Link, usePage } from '@inertiajs/vue3'

// Props untuk menerima data menu dan fungsi kontrol
defineProps({
    item: {
        type: Object,
        required: true,
    },
    filteredDropdown: {
        type: Array,
        default: () => [],
    },
    closeDropdown: {
        type: Function,
        required: true,
    },
});

const { props } = usePage()
const pendingCount = props.auth.pending_user_count || 0
const taskQueueCount = props.auth.task_queue_count || 0
</script>

<template>
    <!-- Container Dropdown dengan Style Premium -->
    <div class="absolute left-0 top-full z-50 w-72 py-2 mt-2 origin-top-left bg-white rounded-2xl shadow-2xl border border-gray-100 ring-1 ring-black/5">
        <!-- Header Dropdown (Nama Menu Utama) -->
        <div class="px-4 py-3 mb-1 border-b border-gray-50 bg-gray-50/30 rounded-t-2xl">
            <div class="flex items-center">
                <div class="w-8 h-8 flex items-center justify-center bg-blue-50 rounded-lg mr-3">
                    <component :is="item.icon" class="w-4 h-4 text-blue-600" />
                </div>
                <div>
                    <span class="text-sm font-bold text-slate-900 uppercase tracking-tight">
                        {{ item.name }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Daftar Sub-menu dengan max-height dan scroll -->
        <div class="px-2 py-1 space-y-1 max-h-80 overflow-y-auto">
            <Link
                v-for="subItem in filteredDropdown"
                :key="subItem.name"
                :href="subItem.href"
                @click="closeDropdown"
                class="flex items-start p-3 text-sm text-slate-600 hover:bg-blue-50/80 hover:text-blue-700 transition-all duration-200 rounded-xl group"
            >
                <div class="flex items-start flex-1 min-w-0">
                    <!-- Icon Sub-menu -->
                    <div class="relative flex-shrink-0 mr-3">
                        <div class="w-9 h-9 flex items-center justify-center bg-slate-50 border border-gray-100 rounded-lg group-hover:bg-white group-hover:shadow-sm transition-all duration-200">
                            <component :is="subItem.icon" class="w-4 h-4 text-slate-400 group-hover:text-blue-600 transition-colors duration-200" />
                        </div>
                    </div>

                    <!-- Teks & Deskripsi -->
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <div class="font-bold text-slate-800 group-hover:text-blue-700 transition-colors duration-200 text-sm">
                                {{ subItem.name }}
                            </div>

                            <!-- Badge for Tiket Saya -->
                            <span 
                                v-if="subItem.name === 'Tiket Saya' && pendingCount > 0"
                                class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm group-hover:scale-110 transition-transform"
                            >
                                {{ pendingCount }}
                            </span>
                            <!-- Badge for Daftar Tugas -->
                            <span 
                                v-if="subItem.name === 'Daftar Tugas' && taskQueueCount > 0"
                                class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold text-white bg-orange-500 rounded-full shadow-sm group-hover:scale-110 transition-transform"
                            >
                                {{ taskQueueCount }}
                            </span>
                        </div>

                        <!-- Deskripsi -->
                        <div
                            v-if="subItem.description"
                            class="text-[11px] leading-relaxed text-slate-500 mt-0.5 line-clamp-1 group-hover:text-blue-600/70 transition-colors duration-200"
                        >
                            {{ subItem.description }}
                        </div>
                    </div>
                </div>
            </Link>
        </div>
    </div>
</template>
