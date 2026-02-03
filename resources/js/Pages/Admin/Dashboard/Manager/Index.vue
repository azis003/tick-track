<script setup>
// app name
const appName = import.meta.env.VITE_APP_NAME

// import Head dari Inertia
import { Head } from "@inertiajs/vue3"

// import LayoutAdmin
import LayoutAdmin from "@/Layouts/LayoutAdmin.vue";

// import icons
import { 
    BarChart3, 
    Users, 
    CheckCircle2, 
    Clock,
    TrendingUp,
    TrendingDown,
    Minus,
    FileCheck,
    AlertCircle
} from 'lucide-vue-next';

// Props dari controller
defineProps({
    stats: Object,
    pendingApprovals: Array,
    teamPerformance: Array,
})
</script>

<template>
    <Head :title="`Dashboard Manager - ${appName}`" />

    <LayoutAdmin>
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Manager TI</h1>
            <p class="mt-2 text-gray-600">Overview performa tim dan approval tiket.</p>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Tiket -->
            <div class="relative overflow-hidden p-6 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg text-white">
                <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full -translate-y-8 translate-x-8"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <BarChart3 class="w-8 h-8 opacity-80" />
                        <span class="flex items-center text-sm bg-white/20 px-2 py-1 rounded-full">
                            <TrendingUp class="w-4 h-4 mr-1" />
                            +12%
                        </span>
                    </div>
                    <p class="text-4xl font-bold mt-4">{{ stats?.totalTickets || 0 }}</p>
                    <p class="text-indigo-100 mt-1">Total Tiket Bulan Ini</p>
                </div>
            </div>

            <!-- Resolved -->
            <div class="relative overflow-hidden p-6 bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg text-white">
                <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full -translate-y-8 translate-x-8"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <CheckCircle2 class="w-8 h-8 opacity-80" />
                        <span class="flex items-center text-sm bg-white/20 px-2 py-1 rounded-full">
                            <TrendingUp class="w-4 h-4 mr-1" />
                            +8%
                        </span>
                    </div>
                    <p class="text-4xl font-bold mt-4">{{ stats?.resolvedTickets || 0 }}</p>
                    <p class="text-green-100 mt-1">Tiket Selesai</p>
                </div>
            </div>

            <!-- Avg Resolution Time -->
            <div class="relative overflow-hidden p-6 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg text-white">
                <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full -translate-y-8 translate-x-8"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <Clock class="w-8 h-8 opacity-80" />
                        <span class="flex items-center text-sm bg-white/20 px-2 py-1 rounded-full">
                            <TrendingDown class="w-4 h-4 mr-1" />
                            -15%
                        </span>
                    </div>
                    <p class="text-4xl font-bold mt-4">{{ stats?.avgResolutionTime || '2.5' }}h</p>
                    <p class="text-blue-100 mt-1">Rata-rata Waktu Resolusi</p>
                </div>
            </div>

            <!-- Team Members -->
            <div class="relative overflow-hidden p-6 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg text-white">
                <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full -translate-y-8 translate-x-8"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <Users class="w-8 h-8 opacity-80" />
                        <span class="flex items-center text-sm bg-white/20 px-2 py-1 rounded-full">
                            <Minus class="w-4 h-4 mr-1" />
                            Stabil
                        </span>
                    </div>
                    <p class="text-4xl font-bold mt-4">{{ stats?.teamMembers || 0 }}</p>
                    <p class="text-purple-100 mt-1">Anggota Tim Aktif</p>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Pending Approvals -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-orange-100 rounded-lg">
                            <FileCheck class="w-5 h-5 text-orange-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Menunggu Approval</h2>
                            <p class="text-sm text-gray-500">Tiket yang memerlukan persetujuan</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 text-sm font-medium bg-orange-100 text-orange-700 rounded-full">
                        {{ pendingApprovals?.length || 0 }}
                    </span>
                </div>
                <div class="p-6">
                    <div v-if="pendingApprovals && pendingApprovals.length > 0" class="space-y-3">
                        <div 
                            v-for="approval in pendingApprovals" 
                            :key="approval.id"
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                        >
                            <div>
                                <p class="font-medium text-gray-900">{{ approval.title }}</p>
                                <p class="text-sm text-gray-500">{{ approval.ticket_number }} • {{ approval.requester }}</p>
                            </div>
                            <div class="flex gap-2">
                                <button class="px-3 py-1.5 text-sm font-medium text-white bg-green-500 rounded-lg hover:bg-green-600">
                                    Approve
                                </button>
                                <button class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                                    Review
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8">
                        <CheckCircle2 class="mx-auto w-12 h-12 text-green-500" />
                        <p class="mt-4 text-gray-500">Tidak ada tiket menunggu approval</p>
                    </div>
                </div>
            </div>

            <!-- Team Performance -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <Users class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Performa Tim</h2>
                        <p class="text-sm text-gray-500">Statistik anggota tim bulan ini</p>
                    </div>
                </div>
                <div class="p-6">
                    <div v-if="teamPerformance && teamPerformance.length > 0" class="space-y-4">
                        <div 
                            v-for="member in teamPerformance" 
                            :key="member.id"
                            class="flex items-center gap-4"
                        >
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                                {{ member.name?.charAt(0) }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="font-medium text-gray-900">{{ member.name }}</p>
                                    <span class="text-sm text-gray-500">{{ member.resolved }} selesai</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2 rounded-full transition-all"
                                        :style="{ width: `${(member.resolved / 20) * 100}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8">
                        <AlertCircle class="mx-auto w-12 h-12 text-gray-400" />
                        <p class="mt-4 text-gray-500">Belum ada data performa</p>
                    </div>
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>
