<script setup>
// import icons dari lucide-vue-next
import { Activity, ArrowRight, Clock, User } from 'lucide-vue-next'

// import Link dari Inertia
import { Link } from '@inertiajs/vue3'

// props
const props = defineProps({
    recentActivities: {
        type: Array,
        default: () => [],
    },
})
</script>

<template>
    <div class="bg-white shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 bg-blue-100 rounded-lg">
                        <Activity class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Aktivitas Terbaru
                        </h3>
                        <p class="text-sm text-gray-600">
                            Update real-time sistem
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Activity List -->
            <div v-if="recentActivities.length > 0" class="space-y-4">
                <div
                    v-for="(activity, index) in recentActivities"
                    :key="index"
                    class="flex items-start pb-4 border-b border-gray-100 last:border-0 last:pb-0"
                >
                    <!-- Timeline Dot -->
                    <div class="relative flex-shrink-0 mr-4">
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        </div>
                        <!-- Connecting Line (kecuali item terakhir) -->
                        <div
                            v-if="index !== recentActivities.length - 1"
                            class="absolute top-8 left-1/2 w-0.5 h-full bg-gray-200 -ml-px"
                        ></div>
                    </div>

                    <!-- Activity Content -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900">
                            {{ activity.description }}
                        </p>
                        <div class="flex items-center mt-1 space-x-3 text-xs text-gray-500">
                            <span v-if="activity.user" class="flex items-center">
                                <User class="w-3 h-3 mr-1" />
                                {{ activity.user }}
                            </span>
                            <span class="flex items-center">
                                <Clock class="w-3 h-3 mr-1" />
                                {{ activity.created_at }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-8 text-center">
                <Activity class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                <p class="text-gray-500">Belum ada aktivitas</p>
                <p class="mt-1 text-sm text-gray-400">
                    Aktivitas sistem akan muncul di sini
                </p>
            </div>

            <!-- View All Button -->
            <div v-if="recentActivities.length > 0" class="pt-4 mt-4 border-t border-gray-100">
                <Link
                    href="/admin/activities"
                    class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-blue-600 transition-colors bg-blue-50 hover:bg-blue-100 rounded-lg"
                >
                    Lihat Semua Aktivitas
                    <ArrowRight class="w-4 h-4 ml-2" />
                </Link>
            </div>
        </div>
    </div>
</template>
