<script setup>
// app name
const appName = import.meta.env.VITE_APP_NAME

// import Head dari Inertia
import { Head } from "@inertiajs/vue3"

// import LayoutAdmin
import LayoutAdmin from "@/Layouts/LayoutAdmin.vue";

// import icons
import { 
    ClipboardList, 
    Play, 
    Clock, 
    CheckCircle2,
    AlertTriangle,
    ArrowRight
} from 'lucide-vue-next';

// Props dari controller
defineProps({
    stats: Object,
    assignedTickets: Array,
})

// Helper untuk priority badge
const getPriorityClass = (priority) => {
    const classes = {
        'Critical': 'bg-red-100 text-red-700 border-red-200',
        'High': 'bg-orange-100 text-orange-700 border-orange-200',
        'Medium': 'bg-yellow-100 text-yellow-700 border-yellow-200',
        'Low': 'bg-green-100 text-green-700 border-green-200',
    };
    return classes[priority] || 'bg-gray-100 text-gray-700 border-gray-200';
};
</script>

<template>
    <Head :title="`Dashboard Teknisi - ${appName}`" />

    <LayoutAdmin>
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Teknisi</h1>
            <p class="mt-2 text-gray-600">Kelola tiket yang ditugaskan kepada Anda.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Ditugaskan -->
            <div class="relative overflow-hidden p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-all group">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-indigo-100 rounded-full opacity-50 group-hover:opacity-70 transition-opacity"></div>
                <div class="relative flex items-center">
                    <div class="p-3 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-md">
                        <ClipboardList class="w-6 h-6 text-white" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Ditugaskan</p>
                        <p class="text-3xl font-bold text-gray-900">{{ stats?.assigned || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Dalam Proses -->
            <div class="relative overflow-hidden p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-all group">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-100 rounded-full opacity-50 group-hover:opacity-70 transition-opacity"></div>
                <div class="relative flex items-center">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-md">
                        <Play class="w-6 h-6 text-white" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Dalam Proses</p>
                        <p class="text-3xl font-bold text-gray-900">{{ stats?.inProgress || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="relative overflow-hidden p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-all group">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-orange-100 rounded-full opacity-50 group-hover:opacity-70 transition-opacity"></div>
                <div class="relative flex items-center">
                    <div class="p-3 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-md">
                        <Clock class="w-6 h-6 text-white" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pending</p>
                        <p class="text-3xl font-bold text-gray-900">{{ stats?.pending || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Selesai -->
            <div class="relative overflow-hidden p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-all group">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-green-100 rounded-full opacity-50 group-hover:opacity-70 transition-opacity"></div>
                <div class="relative flex items-center">
                    <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-md">
                        <CheckCircle2 class="w-6 h-6 text-white" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Selesai</p>
                        <p class="text-3xl font-bold text-gray-900">{{ stats?.resolved || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Tickets -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-100 rounded-lg">
                        <ClipboardList class="w-5 h-5 text-indigo-600" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Tiket Ditugaskan</h2>
                        <p class="text-sm text-gray-500">Tiket yang perlu Anda kerjakan</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-sm font-medium bg-indigo-100 text-indigo-700 rounded-full">
                    {{ assignedTickets?.length || 0 }} tiket
                </span>
            </div>
            <div class="p-6">
                <div v-if="assignedTickets && assignedTickets.length > 0" class="space-y-4">
                    <div 
                        v-for="ticket in assignedTickets" 
                        :key="ticket.id"
                        class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-all group cursor-pointer"
                    >
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-mono text-gray-500">{{ ticket.ticket_number }}</span>
                                <span 
                                    class="px-2 py-0.5 text-xs font-medium rounded-full border"
                                    :class="getPriorityClass(ticket.priority)"
                                >
                                    {{ ticket.priority || 'Normal' }}
                                </span>
                            </div>
                            <p class="font-medium text-gray-900 group-hover:text-indigo-600 transition-colors">
                                {{ ticket.title }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Dari: {{ ticket.user }} • {{ ticket.created_at }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all shadow-sm hover:shadow-md flex items-center gap-2">
                                <Play class="w-4 h-4" />
                                Mulai
                            </button>
                            <ArrowRight class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all" />
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12">
                    <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <CheckCircle2 class="w-8 h-8 text-green-600" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Tidak ada tiket menunggu</h3>
                    <p class="mt-2 text-gray-500">Semua tiket sudah dikerjakan. Kerja bagus! 🎉</p>
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>
