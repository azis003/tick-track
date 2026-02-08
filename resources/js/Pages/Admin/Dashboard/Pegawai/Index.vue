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

    </LayoutAdmin>
</template>
