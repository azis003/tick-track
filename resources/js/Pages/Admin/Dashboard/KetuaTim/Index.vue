<script setup>
// app name
const appName = import.meta.env.VITE_APP_NAME

// import Head dari Inertia
import { Head } from "@inertiajs/vue3"

// import LayoutAdmin
import LayoutAdmin from "@/Layouts/LayoutAdmin.vue";

// import icons
import { 
    Users, 
    ClipboardList, 
    Clock, 
    CheckCircle2,
    TrendingUp,
    AlertCircle,
    UserCircle
} from 'lucide-vue-next';

// Props dari controller
defineProps({
    stats: Object,
    teamMembers: Array,
    recentTickets: Array,
})

// Helper untuk status badge
const getStatusClass = (status) => {
    const classes = {
        'new': 'bg-indigo-100 text-indigo-700',
        'in_progress': 'bg-blue-100 text-blue-700',
        'pending_user': 'bg-orange-100 text-orange-700',
        'pending_external': 'bg-orange-100 text-orange-700',
        'resolved': 'bg-green-100 text-green-700',
        'closed': 'bg-gray-100 text-gray-700',
    };
    return classes[status] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head :title="`Dashboard Ketua Tim - ${appName}`" />

    <LayoutAdmin>
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Ketua Tim</h1>
            <p class="mt-2 text-gray-600">Pantau tiket dan kinerja anggota tim Anda.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Anggota -->
            <div class="p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow border-l-4 border-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Anggota</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats?.totalMembers || 0 }}</p>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-xl">
                        <Users class="w-6 h-6 text-indigo-600" />
                    </div>
                </div>
            </div>

            <!-- Tiket Tim -->
            <div class="p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tiket Tim</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats?.totalTickets || 0 }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-xl">
                        <ClipboardList class="w-6 h-6 text-blue-600" />
                    </div>
                </div>
            </div>

            <!-- Dalam Proses -->
            <div class="p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Dalam Proses</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats?.inProgress || 0 }}</p>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-xl">
                        <Clock class="w-6 h-6 text-orange-600" />
                    </div>
                </div>
            </div>

            <!-- Selesai Bulan Ini -->
            <div class="p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Selesai Bulan Ini</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats?.closedThisMonth || 0 }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-xl">
                        <CheckCircle2 class="w-6 h-6 text-green-600" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Team Members -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="p-2 bg-indigo-100 rounded-lg">
                        <Users class="w-5 h-5 text-indigo-600" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Anggota Tim</h2>
                        <p class="text-sm text-gray-500">Status tiket per anggota</p>
                    </div>
                </div>
                <div class="p-6">
                    <div v-if="teamMembers && teamMembers.length > 0" class="space-y-4">
                        <div 
                            v-for="member in teamMembers" 
                            :key="member.id"
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                                    {{ member.name?.charAt(0) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ member.name }}</p>
                                    <p class="text-sm text-gray-500">{{ member.position || 'Pegawai' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-sm">
                                <div class="text-center">
                                    <p class="font-bold text-blue-600">{{ member.open || 0 }}</p>
                                    <p class="text-gray-500">Open</p>
                                </div>
                                <div class="text-center">
                                    <p class="font-bold text-green-600">{{ member.closed || 0 }}</p>
                                    <p class="text-gray-500">Closed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8">
                        <UserCircle class="mx-auto w-12 h-12 text-gray-400" />
                        <p class="mt-4 text-gray-500">Belum ada anggota tim</p>
                    </div>
                </div>
            </div>

            <!-- Recent Team Tickets -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <ClipboardList class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Tiket Terbaru Tim</h2>
                        <p class="text-sm text-gray-500">Tiket dari anggota tim Anda</p>
                    </div>
                </div>
                <div class="p-6">
                    <div v-if="recentTickets && recentTickets.length > 0" class="space-y-3">
                        <div 
                            v-for="ticket in recentTickets" 
                            :key="ticket.id"
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                        >
                            <div>
                                <p class="font-medium text-gray-900">{{ ticket.title }}</p>
                                <p class="text-sm text-gray-500">{{ ticket.ticket_number }} • {{ ticket.reporter }}</p>
                            </div>
                            <span 
                                class="px-3 py-1 text-xs font-medium rounded-full"
                                :class="getStatusClass(ticket.status)"
                            >
                                {{ ticket.status_label }}
                            </span>
                        </div>
                    </div>
                    <div v-else class="text-center py-8">
                        <AlertCircle class="mx-auto w-12 h-12 text-gray-400" />
                        <p class="mt-4 text-gray-500">Belum ada tiket dari tim</p>
                    </div>
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>
