<script setup>
// app name
const appName = import.meta.env.VITE_APP_NAME

// import Head dari Inertia
import { Head } from "@inertiajs/vue3"

// import LayoutAdmin
import LayoutAdmin from "@/Layouts/LayoutAdmin.vue";

// Props dari controller
defineProps({
    stats: Object,
    recentTickets: Array,
})
</script>

<template>
    <Head :title="`Dashboard - ${appName}`" />

    <LayoutAdmin>
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Pegawai</h1>
            <p class="mt-2 text-gray-600">Selamat datang di TickTrack. Berikut adalah ringkasan tiket Anda.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Tiket -->
            <div class="p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-indigo-100 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Tiket</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats?.total || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Dalam Proses -->
            <div class="p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Dalam Proses</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats?.inProgress || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats?.pending || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Selesai -->
            <div class="p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Selesai</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats?.closed || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tickets -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Tiket Terbaru Saya</h2>
            </div>
            <div class="p-6">
                <div v-if="recentTickets && recentTickets.length > 0" class="space-y-4">
                    <div 
                        v-for="ticket in recentTickets" 
                        :key="ticket.id"
                        class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        <div>
                            <p class="font-medium text-gray-900">{{ ticket.title }}</p>
                            <p class="text-sm text-gray-500">{{ ticket.ticket_number }} • {{ ticket.created_at }}</p>
                        </div>
                        <span 
                            class="px-3 py-1 text-xs font-medium rounded-full"
                            :class="{
                                'bg-indigo-100 text-indigo-700': ticket.status === 'new',
                                'bg-blue-100 text-blue-700': ticket.status === 'in_progress',
                                'bg-orange-100 text-orange-700': ticket.status === 'pending_user' || ticket.status === 'pending_vendor',
                                'bg-green-100 text-green-700': ticket.status === 'resolved' || ticket.status === 'closed',
                            }"
                        >
                            {{ ticket.status_label }}
                        </span>
                    </div>
                </div>
                <div v-else class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="mt-4 text-gray-500">Belum ada tiket yang dibuat</p>
                    <a href="/admin/tickets/create" class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Buat Tiket Baru
                    </a>
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>
